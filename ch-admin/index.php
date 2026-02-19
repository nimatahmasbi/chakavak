<?php
require_once 'config.php';

// بررسی لاگین بودن ادمین
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// تشخیص صفحه فعلی
$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت چکاوک</title>
    <script src="../libs/tailwind.js"></script>
    <link href="../libs/vazir/font.css" rel="stylesheet">
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
        .active-menu { @apply bg-blue-50 text-blue-600 border-r-4 border-blue-600 font-bold; }
    </style>
    <?php
        // محاسبه تعداد پیام‌های خوانده نشده برای ادمین
        $adminId = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetchColumn() ?: 1;
        $unreadCount = $pdo->query("SELECT COUNT(*) FROM messages WHERE target_id=$adminId AND is_read=0")->fetchColumn();
        ?>
        
        <a href="../index.php" target="_blank" style="position: relative; margin-left: 20px; display: inline-block; text-decoration: none; color: #333; font-size: 22px;">
            🔔
            <?php if($unreadCount > 0): ?>
                <span style="position: absolute; top: -5px; right: -10px; background: #dc3545; color: white; font-size: 11px; font-weight: bold; padding: 2px 6px; border-radius: 50%; font-family: Tahoma;">
                    <?= $unreadCount ?>
                </span>
    <?php endif; ?>
</a>
</head>
<body class="bg-gray-100 h-screen flex overflow-hidden text-gray-800" data-page="<?php echo $page; ?>">

    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-20 hidden md:hidden glass" onclick="toggleSidebar()"></div>

    <aside id="sidebar" class="bg-white w-64 h-full flex flex-col shadow-xl z-30 fixed right-0 top-0 transition-transform duration-300 transform translate-x-full md:translate-x-0 md:static border-l border-gray-200">
        <div class="h-16 flex items-center justify-center border-b border-gray-100 shrink-0">
            <h1 class="text-xl font-black text-blue-600 flex items-center gap-2">⚡️ مدیریت چکاوک</h1>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <a href="?page=dashboard" class="flex items-center gap-3 px-3 py-3 rounded-lg transition hover:bg-gray-50 <?php echo $page=='dashboard'?'active-menu':'text-gray-600'; ?>">
                <span class="text-lg">📊</span> داشبورد
            </a>
            <a href="?page=users" class="flex items-center gap-3 px-3 py-3 rounded-lg transition hover:bg-gray-50 <?php echo $page=='users'?'active-menu':'text-gray-600'; ?>">
                <span class="text-lg">👥</span> کاربران
            </a>
            <a href="?page=groups" class="flex items-center gap-3 px-3 py-3 rounded-lg transition hover:bg-gray-50 <?php echo $page=='groups'?'active-menu':'text-gray-600'; ?>">
                <span class="text-lg">📢</span> گروه‌ها
            </a>
            <a href="?page=settings" class="flex items-center gap-3 px-3 py-3 rounded-lg transition hover:bg-gray-50 <?php echo $page=='settings'?'active-menu':'text-gray-600'; ?>">
                <span class="text-lg">⚙️</span> تنظیمات
            </a>
        </nav>

        <div class="p-4 border-t border-gray-100 shrink-0">
            <a href="login.php?logout=1" class="flex items-center gap-3 px-3 py-2 rounded-lg text-red-500 hover:bg-red-50 transition text-sm font-bold">
                <span>🚪</span> خروج
            </a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col min-w-0 h-full">
        <header class="bg-white h-16 border-b border-gray-200 flex items-center justify-between px-4 lg:px-8 shrink-0">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-blue-600 p-1">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h2 class="text-lg font-bold text-gray-700 hidden sm:block">
                    <?php 
                        if($page=='dashboard') echo 'نمای کلی سیستم';
                        elseif($page=='users') echo 'مدیریت کاربران';
                        elseif($page=='groups') echo 'مدیریت گفتگوها';
                        elseif($page=='settings') echo 'تنظیمات پیشرفته';
                    ?>
                </h2>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-xs text-gray-400 bg-gray-50 px-3 py-1 rounded-full border border-gray-100"><?php echo date('Y/m/d'); ?></div>
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold border border-blue-200">A</div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto custom-scrollbar p-4 lg:p-8">
            <?php if ($page == 'dashboard'): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between h-32 hover:shadow-md transition">
                        <div class="text-gray-400 text-xs font-bold uppercase tracking-wider">کل کاربران</div>
                        <div class="flex justify-between items-end">
                            <div class="text-3xl font-black text-gray-800" id="totalUsers">...</div>
                            <span class="text-2xl text-blue-100">👥</span>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between h-32 hover:shadow-md transition">
                        <div class="text-gray-400 text-xs font-bold uppercase tracking-wider">گروه‌های فعال</div>
                        <div class="flex justify-between items-end">
                            <div class="text-3xl font-black text-gray-800" id="totalGroups">...</div>
                            <span class="text-2xl text-purple-100">💬</span>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between h-32 hover:shadow-md transition">
                        <div class="text-gray-400 text-xs font-bold uppercase tracking-wider">کانال‌ها</div>
                        <div class="flex justify-between items-end">
                            <div class="text-3xl font-black text-gray-800" id="totalChannels">...</div>
                            <span class="text-2xl text-green-100">📢</span>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between h-32 hover:shadow-md transition">
                        <div class="text-gray-400 text-xs font-bold uppercase tracking-wider">وضعیت سرور</div>
                        <div class="flex justify-between items-end">
                            <div class="text-xl font-black text-green-500">آنلاین</div>
                            <span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </span>
                        </div>
                    </div>
                </div>
            <?php elseif ($page == 'users'): ?>
                <?php include 'sections/users.php'; ?>
            <?php elseif ($page == 'groups'): ?>
                <?php include 'sections/groups.php'; ?>
            <?php elseif ($page == 'settings'): ?>
                <?php include 'sections/settings.php'; ?>
            <?php endif; ?>
        </div>
    </main>

    <div id="adminModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm animate-fade-in">
        <div class="bg-white w-full max-w-lg rounded-2xl p-0 shadow-2xl relative flex flex-col max-h-[90vh]">
            <div class="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-2xl">
                <h3 class="font-bold text-gray-700">جزئیات</h3>
                <button onclick="document.getElementById('adminModal').classList.add('hidden')" class="text-gray-400 hover:text-red-500 text-xl">&times;</button>
            </div>
            <div id="adminModalContent" class="p-6 overflow-y-auto custom-scrollbar"></div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>

    <script type="module" src="../assets/js/admin.js?v=<?php echo time(); ?>"></script>
</body>

</html>
