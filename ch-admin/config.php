<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['lifetime'=>2592000,'path'=>'/','domain'=>'','secure'=>false,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
date_default_timezone_set('Asia/Tehran');
require_once __DIR__ . '/db.php';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) { if (!defined('INSTALLING')) die("DB Error: " . $e->getMessage()); }

if (isset($pdo)) {
    try {
        // ساخت جداول پایه (اگر نباشند)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (`id` int(11) AUTO_INCREMENT PRIMARY KEY, `username` varchar(50), `phone` varchar(20), `password` varchar(255), `first_name` varchar(50), `last_name` varchar(50), `avatar` varchar(255) DEFAULT 'default', `bio` text, `is_approved` tinyint(1) DEFAULT 0, `created_at` timestamp DEFAULT CURRENT_TIMESTAMP, UNIQUE(`username`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `groups` (`id` int(11) AUTO_INCREMENT PRIMARY KEY, `name` varchar(100), `avatar` varchar(255) DEFAULT 'default', `type` varchar(20) DEFAULT 'group', `chat_key` text, `is_banned` tinyint(1) DEFAULT 0, `created_at` timestamp DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `group_members` (`id` int(11) AUTO_INCREMENT PRIMARY KEY, `group_id` int, `user_id` int, `role` varchar(20) DEFAULT 'member', `joined_at` timestamp DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `messages` (`id` int(11) AUTO_INCREMENT PRIMARY KEY, `sender_id` int, `target_id` int, `type` varchar(20), `message` text, `file_path` varchar(255), `file_type` varchar(20), `is_read` tinyint(1) DEFAULT 0, `created_at` timestamp DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (`id` int(11) AUTO_INCREMENT PRIMARY KEY, `username` varchar(50), `password` varchar(255), UNIQUE(`username`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (`key_name` varchar(50) PRIMARY KEY, `value` text) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // --- اصلاحات و تعمیرات خودکار ---
        
        // 1. تبدیل is_banned به is_approved (استانداردسازی)
        try {
            $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_banned'")->fetch();
            if ($cols) {
                // اگر ستون قدیمی هست، تغییر نام بده و مقادیر را برعکس کن (بن=1 -> تایید=0)
                $pdo->exec("ALTER TABLE users CHANGE is_banned is_approved TINYINT(1) DEFAULT 0");
                $pdo->exec("UPDATE users SET is_approved = NOT is_approved"); 
            }
        } catch(Exception $e) {}

        // 2. مطمئن شویم ستون is_approved وجود دارد
        try {
            $pdo->query("SELECT is_approved FROM users LIMIT 1");
        } catch (Exception $e) {
            $pdo->exec("ALTER TABLE users ADD COLUMN is_approved TINYINT(1) DEFAULT 0");
        }

    } catch (Exception $e) {}
}
?>