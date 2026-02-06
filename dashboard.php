<?php 
require 'ch-admin/config.php'; 

if(!isset($_SESSION['uid'])) {
    if(isset($_SESSION['admin'])) { $_SESSION['uid'] = 1; } 
    else { header("Location: index.php"); exit; }
}

$me = $pdo->query("SELECT * FROM users WHERE id=".$_SESSION['uid'])->fetch(); 
if (!$me) { $me = ['id' => 1, 'first_name' => 'Admin', 'last_name' => '', 'username' => 'admin', 'avatar' => 'default', 'phone' => '', 'bio' => 'Admin']; }

// 1. سربرگ
require 'includes/header.php';

// 2. صفحه اصلی (لیست چت‌ها)
require 'includes/screen_main.php';

// 3. صفحه چت (داخل گفتگو)
require 'includes/screen_chat.php';

// 4. فوتر (مودال‌ها و اسکریپت‌ها)
require 'includes/footer.php';
?>