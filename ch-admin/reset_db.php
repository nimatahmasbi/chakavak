<?php
// اتصال به دیتابیس
require_once 'db.php';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo '<body style="font-family: monospace; direction: ltr; background: #1a1a1a; color: #0f0; padding: 20px;">';
    echo "<h1>--- DATABASE RESET TOOL ---</h1>";

    // 1. حذف جداول (به ترتیب کلید خارجی)
    $tables = [
        'push_subscriptions', 'user_contacts', 'messages', 'group_members', 
        'groups', 'user_tokens', 'admins', 'users', 'settings'
    ];
    
    foreach($tables as $t) {
        $pdo->exec("DROP TABLE IF EXISTS `$t`");
        echo "[DELETE] Table '$t' dropped.<br>";
    }
    echo "<hr>";

    // 2. ساخت جداول جدید

    // جدول کاربران (با فیلد is_approved برای مدیریت تایید)
    $pdo->exec("CREATE TABLE `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL,
        `phone` varchar(20) NOT NULL,
        `password` varchar(255) NOT NULL,
        `first_name` varchar(50) NOT NULL,
        `last_name` varchar(50) DEFAULT NULL,
        `avatar` varchar(255) DEFAULT 'default',
        `bio` text,
        `is_approved` tinyint(1) DEFAULT 0 COMMENT '0: Pending/Banned, 1: Active',
        `social_telegram` varchar(100) DEFAULT NULL,
        `social_instagram` varchar(100) DEFAULT NULL,
        `social_whatsapp` varchar(100) DEFAULT NULL,
        `social_linkedin` varchar(100) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `username` (`username`),
        UNIQUE KEY `phone` (`phone`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[CREATE] Table 'users' created.<br>";

    // جدول ادمین‌ها
    $pdo->exec("CREATE TABLE `admins` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL,
        `password` varchar(255) NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[CREATE] Table 'admins' created.<br>";

    // جدول گروه‌ها
    $pdo->exec("CREATE TABLE `groups` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `avatar` varchar(255) DEFAULT 'default',
        `type` varchar(20) DEFAULT 'group',
        `chat_key` text,
        `is_banned` tinyint(1) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[CREATE] Table 'groups' created.<br>";

    // جدول اعضای گروه
    $pdo->exec("CREATE TABLE `group_members` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `group_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        `role` varchar(20) DEFAULT 'member',
        `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[CREATE] Table 'group_members' created.<br>";

    // جدول پیام‌ها
    $pdo->exec("CREATE TABLE `messages` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `sender_id` int(11) NOT NULL,
        `target_id` int(11) NOT NULL,
        `type` varchar(20) NOT NULL,
        `message` text,
        `file_path` varchar(255) DEFAULT NULL,
        `file_type` varchar(20) DEFAULT NULL,
        `is_read` tinyint(1) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[CREATE] Table 'messages' created.<br>";

    // سایر جداول
    $pdo->exec("CREATE TABLE `user_contacts` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `owner_id` int(11) NOT NULL,
        `contact_id` int(11) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_contact` (`owner_id`,`contact_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE `user_tokens` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `token` varchar(64) NOT NULL,
        `expires_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE `settings` (
        `key_name` varchar(50) NOT NULL,
        `value` text,
        PRIMARY KEY (`key_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("INSERT INTO `settings` (`key_name`, `value`) VALUES ('ippanel_key', ''), ('ippanel_line', ''), ('enable_2fa', '0')");

    $pdo->exec("CREATE TABLE `push_subscriptions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `endpoint` text NOT NULL,
        `p256dh` text NOT NULL,
        `auth` text NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_endpoint` (`endpoint`(255))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "<hr>";

    // 3. ساخت ادمین پیش‌فرض
    $adminUser = "Mr.NT";
    $adminPass = "1020315@";
    $hash = password_hash($adminPass, PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("INSERT INTO `admins` (`username`, `password`) VALUES (?, ?)");
    $stmt->execute([$adminUser, $hash]);

    echo "<h2 style='color:#fff'>SUCCESS! Admin Created.</h2>";
    echo "User: $adminUser <br> Pass: $adminPass";

} catch (PDOException $e) {
    echo "<h2 style='color:red'>ERROR: " . $e->getMessage() . "</h2>";
}
?>