<?php
// تنظیمات کوکی سراسری
session_set_cookie_params([
    'lifetime' => 86400 * 30,
    'path' => '/', 
    'domain' => '', 
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
date_default_timezone_set('Asia/Tehran');
require_once __DIR__ . '/db.php';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// تعمیر خودکار دیتابیس
if(isset($pdo)){
    // جدول تنظیمات
    try { $pdo->query("SELECT value FROM settings LIMIT 1"); } catch(Exception $e){ 
        $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (`key_name` varchar(50) NOT NULL, `value` text, PRIMARY KEY (`key_name`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $pdo->exec("INSERT IGNORE INTO settings VALUES ('ippanel_key',''), ('ippanel_line',''), ('enable_2fa','0'), ('enable_passkey','0')");
    }
    
    // جدول مخاطبین
    try { $pdo->query("SELECT id FROM user_contacts LIMIT 1"); } catch(Exception $e){ 
        $pdo->exec("CREATE TABLE IF NOT EXISTS `user_contacts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `owner_id` INT NOT NULL,
            `contact_id` INT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_contact` (`owner_id`, `contact_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    // *** جدول جدید: اشتراک‌های پوش نوتیفیکیشن (VAPID) ***
    try { $pdo->query("SELECT id FROM push_subscriptions LIMIT 1"); } catch(Exception $e){ 
        $pdo->exec("CREATE TABLE IF NOT EXISTS `push_subscriptions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `endpoint` TEXT NOT NULL,
            `p256dh` TEXT NOT NULL,
            `auth` TEXT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_endpoint` (`endpoint`(255))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    // آپدیت‌های جداول قدیمی
    try { $pdo->query("SELECT role FROM group_members LIMIT 1"); } catch(Exception $e){ $pdo->exec("ALTER TABLE group_members ADD COLUMN role VARCHAR(20) DEFAULT 'member'"); }
    try { $pdo->query("SELECT type FROM groups LIMIT 1"); } catch(Exception $e){ $pdo->exec("ALTER TABLE groups ADD COLUMN type VARCHAR(20) DEFAULT 'group'"); }
    try { $pdo->query("SELECT is_read FROM messages LIMIT 1"); } catch(Exception $e){ $pdo->exec("ALTER TABLE messages ADD COLUMN is_read TINYINT(1) DEFAULT 0"); }
    try { $pdo->query("SELECT is_banned FROM groups LIMIT 1"); } catch(Exception $e){ $pdo->exec("ALTER TABLE groups ADD COLUMN is_banned TINYINT(1) DEFAULT 0"); }
}

function compressImage($source, $destination, $quality) {
    $info = getimagesize($source);
    if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($source);
    elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($source);
    elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($source);
    else return false;
    imagejpeg($image, $destination, $quality);
    return true;
}
?>