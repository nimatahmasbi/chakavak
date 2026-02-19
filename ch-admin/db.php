<?php
/**
 * Security Key Definition | تعریف کلید امنیتی
 * This key is used for system-wide authentication integrity.
 * این کلید برای حفظ یکپارچگی احراز هویت در کل سیستم استفاده می‌شود.
 */
if(!defined('MASTER_SECRET')) {
    // یک کلید تصادفی ۶۴ کاراکتری با ترکیب حروف، اعداد و نمادها
    define('MASTER_SECRET', 'CH-k8#vA!z9$Pq2R_92mN@LpX5*uT1&Z_sQ7wE4yB9vC3xM6jK8nI0oP-v2026_SECURE');
}

/**
 * Database Connection Parameters | پارامترهای اتصال به پایگاه داده
 */
$db_host|میزبان_دیتابیس = 'localhost';
$db_name|نام_دیتابیس   = 'sir_1';
$db_user|نام_کاربری     = 'sir_1';
$db_pass|رمز_عبور       = 'HLLbqrCOA!+r-NK5';

try {
    $pdo = new PDO("mysql:host=$db_host|میزبان_دیتابیس;dbname=$db_name|نام_دیتابیس;charset=utf8mb4", $db_user|نام_کاربری, $db_pass|رمز_عبور);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed|اتصال به دیتابیس با شکست مواجه شد: " . $e->getMessage());
}
?>
