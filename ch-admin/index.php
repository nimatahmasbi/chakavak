<?php
require_once 'config.php';

// بررسی لاگین بودن ادمین
// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// --- همگام‌سازی نشست (SSO) برای ورود یکپارچه به پیام‌رسان ---
// SSO sync to enter chat without re-login
if (!isset($_SESSION['uid'])) {
    $adminData = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetch();
    if ($adminData) {
        $_SESSION['uid'] = $adminData['id']; // تنظیم شناسه ادمین برای محیط چت
    }
}

// محاسبه پیام‌های خوانده نشده مدیر (برای نمایش در زنگوله)
// Calculate unread messages for admin bell icon
$adminChatId = $_SESSION['uid'] ?? 1;
$unreadCount = $pdo->query("SELECT COUNT(*) FROM messages WHERE target_id=$adminChatId AND is_read=0")->fetchColumn();

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
                
                <a href="../index.php" target="_blank" class="relative flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 transition text-gray-600" title="ورود به پیام‌رسان و مشاهده دایرکت‌ها">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <?php if($unreadCount > 0): ?>
                        <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full animate-pulse">
                            <?php echo $unreadCount; ?>
                        </span>
                    <?php endif; ?>
                </a>

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
                
                <?php
                if (isset($_POST['act']) && $_POST['act'] == 'send_global_announce') {
                    $msg = "📢 اعلان مدیریت:\n" . htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');
                    $activeUsers = $pdo->query("SELECT id FROM users WHERE status=1")->fetchAll(PDO::FETCH_COLUMN);
                    
                    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (?, ?, 'pv', ?, NOW())");
                    foreach ($activeUsers as $uid) {
                        $stmt->execute([$adminChatId, $uid, $msg]);
                    }
                    echo "<div class='bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4'>📢 اعلان به تمام کاربران فعال ارسال شد.</div>";
                }
                ?>
                <div class="bg-white p-6 border border-blue-200 rounded-lg shadow-sm border-r-4 border-r-blue-500 mb-8">
                    <h3 class="mt-0 text-blue-600 font-bold text-lg mb-2">📢 ارسال اعلان همگانی (Broadcasting)</h3>
                    <p class="text-gray-500 text-sm mb-4">با نوشتن در این کادر، پیام شما به دایرکت تمام کاربرانِ تایید شده ارسال می‌گردد.</p>
                    <form method="POST" class="flex flex-col sm:flex-row gap-4">
                        <input type="hidden" name="act" value="send_global_announce">
                        <textarea name="message" required placeholder="متن اعلان خود را اینجا بنویسید..." class="flex-1 p-3 border border-gray-300 rounded outline-none resize-y min-h-[80px] focus:border-blue-500"></textarea>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded transition h-full sm:h-auto whitespace-nowrap">ارسال به همه</button>
                    </form>
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
