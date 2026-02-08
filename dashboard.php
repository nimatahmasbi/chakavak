<?php
require_once 'ch-admin/config.php';
if (!isset($_SESSION['uid'])) { header("Location: index.php"); exit; }
$uid = $_SESSION['uid'];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" class="<?php echo (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>چکاوک</title>
    <link rel="manifest" href="assets/json/manifest.json">
    <link rel="apple-touch-icon" href="assets/img/chakavak.png">
    
    <script src="libs/tailwind.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: {} }
        }
    </script>
    <link href="libs/vazir/font.css" rel="stylesheet">
    <script src="libs/crypto-js.js"></script>
    <link href="assets/css/style.css" rel="stylesheet">
    <script>const MY_ID = <?php echo $uid; ?>;</script>
</head>
<body class="bg-[#f0f2f5] dark:bg-gray-900 h-screen flex overflow-hidden text-gray-800 dark:text-gray-100 transition-colors duration-300">

    <div id="sidebar" class="w-full md:w-[350px] lg:w-[400px] bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 flex flex-col h-full z-10 shrink-0 transition-all duration-300">
        <div class="p-3 bg-white dark:bg-gray-800 flex items-center justify-between shadow-sm z-20 h-[60px]">
            <div class="font-bold text-xl text-blue-600 cursor-pointer select-none px-2" onclick="openModal('settingsModal')">≡ چکاوک</div>
            <div class="relative">
                <input type="text" id="searchInput" placeholder="جستجو..." class="bg-gray-100 dark:bg-gray-700 dark:text-white rounded-full px-4 py-1.5 text-sm w-40 focus:w-56 transition-all outline-none">
            </div>
        </div>
        <div class="flex text-sm font-bold border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <button onclick="filterChats('all')" id="tab-all" class="tab-btn flex-1 py-3 border-b-2 border-transparent hover:bg-gray-100 dark:hover:bg-gray-800 transition active-tab">همه</button>
            <button onclick="filterChats('personal')" id="tab-personal" class="tab-btn flex-1 py-3 border-b-2 border-transparent hover:bg-gray-100 dark:hover:bg-gray-800 transition">شخصی</button>
            <button onclick="filterChats('group')" id="tab-group" class="tab-btn flex-1 py-3 border-b-2 border-transparent hover:bg-gray-100 dark:hover:bg-gray-800 transition">گروه</button>
        </div>
        <div id="chatList" class="flex-1 overflow-y-auto custom-scrollbar"></div>
        <div class="absolute bottom-6 left-6 z-30">
            <button onclick="document.getElementById('addMenu').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white rounded-full w-14 h-14 shadow-lg flex items-center justify-center text-2xl cursor-pointer">+</button>
            <div id="addMenu" class="hidden absolute bottom-16 left-0 bg-white dark:bg-gray-800 rounded-xl shadow-xl py-2 w-48 border border-gray-100 dark:border-gray-700 flex flex-col z-40">
                <button onclick="openModal('contactModal')" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm">👤 مخاطب جدید</button>
                <button onclick="openCreateModal('group')" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm">👥 گروه جدید</button>
                <button onclick="openCreateModal('channel')" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm">📢 کانال جدید</button>
            </div>
        </div>
    </div>

    <div id="screen-chat" class="hidden md:flex flex-1 bg-[#efe7dd] dark:bg-black/30 flex-col h-full relative z-20 transition-all duration-300">
        <?php include 'includes/screen_chat.php'; ?>
    </div>

    <?php 
    include 'includes/modals/settings.php';
    include 'includes/modals/contact.php';
    include 'includes/modals/create.php';
    include 'includes/modals/group_info.php';
    include 'includes/modals/profile.php';
    ?>

    <script type="module" src="assets/js/main.js?v=10"></script>
</body>
</html>