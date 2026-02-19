<?php
// فایل نصب‌کننده دیتابیس با تمامی جداول اصلی و تنظیمات
// Database installer file with all main tables and settings
require_once __DIR__ . '/../ch-admin/db.php';

try {
    // ایجاد جدول مدیران سیستم
    // Create system administrators table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `username` varchar(50) NOT NULL,
      `password` varchar(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // ایجاد جدول تنظیمات پویا
    // Create dynamic settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (
        `s_key` varchar(50) PRIMARY KEY,
        `s_value` text
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // ایجاد جدول اصلی کاربران با تمام فیلدها
    // Create main users table with all fields
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `phone` varchar(20) DEFAULT NULL UNIQUE,
      `username` varchar(50) DEFAULT NULL UNIQUE,
      `password` varchar(255) DEFAULT NULL,
      `first_name` varchar(50) DEFAULT NULL,
      `last_name` varchar(50) DEFAULT NULL,
      `bio` text DEFAULT NULL,
      `avatar` varchar(255) DEFAULT 'default',
      `status` tinyint(1) DEFAULT 1,
      `role` varchar(20) DEFAULT 'user',
      `last_seen` datetime DEFAULT NULL,
      `is_online` tinyint(1) DEFAULT 0,
      `created_at` timestamp NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // ایجاد جدول گروه‌های چت
    // Create chat groups table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `groups` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `name` varchar(100) NOT NULL,
      `avatar` varchar(255) DEFAULT 'default',
      `type` varchar(20) DEFAULT 'group',
      `owner_id` int(11) NOT NULL,
      `created_at` timestamp NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // ایجاد جدول اعضای گروه‌ها
    // Create group members table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `group_members` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `group_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `role` varchar(20) DEFAULT 'member',
      `joined_at` timestamp NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // ایجاد جدول پیام‌های کاربران و گروه‌ها
    // Create users and groups messages table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `messages` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `sender_id` int(11) NOT NULL,
      `target_id` int(11) NOT NULL,
      `type` varchar(20) NOT NULL,
      `message` text DEFAULT NULL,
      `file_path` varchar(255) DEFAULT NULL,
      `file_type` varchar(50) DEFAULT NULL,
      `reply_to` int(11) DEFAULT NULL,
      `is_read` tinyint(1) DEFAULT 0,
      `created_at` timestamp NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // ایجاد جدول اشتراک اعلان‌ها (پوش نوتیفیکیشن)
    // Create push notifications subscriptions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `push_subscriptions` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `user_id` int(11) NOT NULL,
      `endpoint` text NOT NULL,
      `p256dh` varchar(255) NOT NULL,
      `auth` varchar(255) NOT NULL,
      `created_at` timestamp NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // ایجاد جدول مخاطبین ذخیره شده
    // Create saved contacts table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `user_contacts` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `owner_id` int(11) NOT NULL,
      `contact_id` int(11) NOT NULL,
      `saved_name` varchar(100) DEFAULT NULL,
      `created_at` timestamp NULL DEFAULT current_timestamp(),
      UNIQUE KEY `unique_contact` (`owner_id`,`contact_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // ایجاد جدول توکن‌های احراز هویت
    // Create authentication tokens table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `user_tokens` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `user_id` int(11) NOT NULL,
      `token` varchar(255) NOT NULL,
      `expires_at` datetime NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // ثبت مقادیر پیش‌فرض برای تنظیمات
    // Insert default values for settings
    $defaultSettings = [
        ['sms_active', '0'],
        ['registration_enabled', '1'],
        ['forgot_password_enabled', '1'],
        ['security_level', 'high']
    ];
    $setStmt = $pdo->prepare("INSERT IGNORE INTO settings (s_key, s_value) VALUES (?, ?)");
    foreach ($defaultSettings as $setting) { 
        $setStmt->execute($setting); 
    }

    // ایجاد حساب کاربری مدیر کل
    // Create main administrator account
    $adminUser = 'Mr.NT';
    $adminPass = password_hash('1020315@', PASSWORD_DEFAULT);
    
    // بررسی و ثبت در جدول ادمین‌ها
    // Check and insert into admins table
    $checkAdmin = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $checkAdmin->execute([$adminUser]);
    if (!$checkAdmin->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->execute([$adminUser, $adminPass]);
    }
    
    // بررسی و ثبت ادمین در جدول کاربران برای امکان چت کردن
    // Check and insert admin into users table for chatting ability
    $checkUserAdmin = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $checkUserAdmin->execute([$adminUser]);
    if (!$checkUserAdmin->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, 'admin', 1)");
        $stmt->execute([$adminUser, $adminPass]);
    }

    // نمایش پیام موفقیت آمیز
    // Display success message
    echo "<div style='font-family:tahoma; text-align:center; padding:50px;'>";
    echo "<h2 style='color:green;'>نصب با موفقیت انجام شد و تمام جداول ساخته شدند</h2>";
    echo "<h2>Installation Successful and all tables created</h2>";
    echo "<p>Admin: Mr.NT | Pass: 1020315@</p>";
    echo "<p style='color:red;'>هشدار: پوشه install را از روی هاست حذف کنید.</p>";
    echo "</div>";

} catch (PDOException $e) {
    // نمایش خطای ارتباط با دیتابیس
    // Display database connection error
    die("Error in Installation: " . $e->getMessage());
}
?>
