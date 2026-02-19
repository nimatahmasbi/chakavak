<?php
require_once 'ch-admin/config.php';

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['uid'])) { 
    header("Location: index.php"); 
    exit; 
}
$uid = $_SESSION['uid'];

// تنظیمات زبان و دایرکشن
$lang = $_COOKIE['lang'] ?? 'fa';
$dir = ($lang == 'en') ? 'ltr' : 'rtl';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>" class="<?php echo (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>پیام‌رسان چکاوک</title>
    <link rel="manifest" href="assets/json/manifest.json">
    <link rel="apple-touch-icon" href="assets/img/chakavak.png">
    
    <script src="libs/tailwind.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { 
                extend: {
                    colors: { 
                        primary: '#2563eb', 
                        secondary: '#1e40af',
                        chatbg: '#efe7dd' // رنگ پس‌زمینه چت تلگرام/واتساپ
                    },
                    fontFamily: {
                        sans: ['Vazirmatn', 'sans-serif'],
                    }
                } 
            }
        }
    </script>
    <link href="libs/vazir/font.css" rel="stylesheet">
    <script src="libs/crypto-js.js"></script>
    <link href="assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    
    <script>const MY_ID = <?php echo $uid; ?>;</script>
    
    <style>
        /* تنظیمات حیاتی برای تمام صفحه شدن */
        html, body {
            height: 100%;
            height: 100dvh; /* ارتفاع دینامیک برای موبایل */
            margin: 0;
            padding: 0;
            overflow: hidden; /* جلوگیری از اسکرول کلی صفحه */
            font-family: 'Vazirmatn', sans-serif;
        }
        
        /* مخفی کردن اسکرول بار در لیست‌ها ولی حفظ قابلیت اسکرول */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
        
        /* انیمیشن‌ها */
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
    </style>
</head>
<body class="bg-[#f0f2f5] dark:bg-gray-900 text-gray-800 dark:text-gray-100 flex w-full h-full">

    <aside id="sidebar" class="w-full md:w-[380px] bg-white dark:bg-gray-800 border-l dark:border-gray-700 flex flex-col h-full z-30 relative shadow-xl transition-all duration-300">
        
        <div class="h-[64px] min-h-[64px] flex items-center justify-between px-4 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 shrink-0">
            <div class="flex items-center gap-2 cursor-pointer" onclick="openModal('settingsModal')">
                <div class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                </div>
                <span class="font-bold text-lg tracking-tight text-gray-800 dark:text-white">چکاوک</span>
            </div>
            
            <div class="flex-1 mr-4 relative group">
                <input type="text" id="searchInput" placeholder="<?php echo ($lang=='en'?'Search...':'جستجو...'); ?>" class="w-full bg-gray-100 dark:bg-gray-700/50 dark:text-white rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-100 outline-none transition-all pl-8">
                <svg class="w-4 h-4 text-gray-400 absolute left-2 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>

        <div class="flex text-sm font-bold border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shrink-0">
            <button onclick="filterChats('all')" id="tab-all" class="tab-btn flex-1 py-3.5 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition relative active-tab">همه</button>
            <button onclick="filterChats('personal')" id="tab-personal" class="tab-btn flex-1 py-3.5 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition relative">شخصی</button>
            <button onclick="filterChats('group')" id="tab-group" class="tab-btn flex-1 py-3.5 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition relative">گروه</button>
            <button onclick="filterChats('channel')" id="tab-channel" class="tab-btn flex-1 py-3.5 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition relative">کانال</button>
        </div>

        <div id="chatList" class="flex-1 overflow-y-auto custom-scrollbar bg-white dark:bg-gray-800 pb-20">
            <div class="flex flex-col items-center justify-center h-40 text-gray-400">
                <div class="w-6 h-6 border-2 border-gray-300 border-t-blue-600 rounded-full animate-spin mb-2"></div>
                <span class="text-xs">درحال بارگذاری...</span>
            </div>
        </div>

        <div class="absolute bottom-6 left-6 z-50">
            <button onclick="document.getElementById('addMenu').classList.toggle('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white rounded-full w-14 h-14 shadow-lg shadow-blue-600/40 flex items-center justify-center text-2xl cursor-pointer transition transform hover:scale-110 active:scale-95 group">
                <span class="group-hover:rotate-90 transition-transform duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </span>
            </button>
            
            <div id="addMenu" class="hidden absolute bottom-16 left-0 bg-white dark:bg-gray-800 rounded-2xl shadow-xl py-2 w-48 border border-gray-100 dark:border-gray-700 flex flex-col z-50 animate-fade-in origin-bottom-left overflow-hidden">
                <button onclick="openModal('contactModal')" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm text-gray-700 dark:text-gray-200 transition text-right">
                    <span class="text-lg">👤</span> <?php echo ($lang=='en'?'New Contact':'مخاطب جدید'); ?>
                </button>
                <button onclick="openCreateModal('group')" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm text-gray-700 dark:text-gray-200 transition text-right">
                    <span class="text-lg">👥</span> <?php echo ($lang=='en'?'New Group':'گروه جدید'); ?>
                </button>
                <button onclick="openCreateModal('channel')" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm text-gray-700 dark:text-gray-200 transition text-right">
                    <span class="text-lg">📢</span> <?php echo ($lang=='en'?'New Channel':'کانال جدید'); ?>
                </button>
            </div>
        </div>
    </aside>

    <main class="flex-1 h-full relative z-10 bg-[#efe7dd] dark:bg-[#0f0f0f] overflow-hidden flex flex-col min-w-0">
        
        <div class="absolute inset-0 opacity-[0.06] dark:opacity-[0.03] pointer-events-none" style="background-image: url('assets/img/chat-bg.png'); background-size: 400px;"></div>

        <div id="screen-main" class="absolute inset-0 flex items-center justify-center bg-[#f0f2f5] dark:bg-gray-900 z-10 hidden md:flex">
            <?php include 'includes/screen_main.php'; ?>
        </div>

        <div id="screen-chat" class="absolute inset-0 flex flex-col z-20 h-full w-full bg-transparent hidden">
            <?php include 'includes/screen_chat.php'; ?>
        </div>

    </main>

    <?php 
    include 'includes/modals/settings.php';
    include 'includes/modals/contact.php';
    include 'includes/modals/create.php';
    include 'includes/modals/group_info.php';
    include 'includes/modals/profile.php';
    ?>

    <script type="module" src="assets/js/main.js?v=<?php echo time(); ?>"></script>
    
    <script>
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('addMenu');
            const btn = document.querySelector('[onclick*="addMenu"]');
            if (!menu.contains(event.target) && !btn.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>