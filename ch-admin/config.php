<?php
// تنظیمات نشست (Session) - حتماً باید قبل از هر کاری باشد
// این کد باعث می‌شود لاگین ادمین در پوشه api هم معتبر باشد
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0, '/'); // اعتبار در کل دامنه
    session_start();
}

// --- تنظیمات دیتابیس ---
require_once __DIR__ . '/db.php';

// --- اتصال به دیتابیس ---
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    // اگر در حالت API هستیم، خروجی JSON بده
    if (strpos($_SERVER['REQUEST_URI'], 'api/') !== false) {
        header('Content-Type: application/json');
        die(json_encode(['status'=>'error', 'msg'=>'Database Connection Failed']));
    }
    // اگر در پنل هستیم، پیام متنی بده
    die("خطا در اتصال دیتابیس: " . $e->getMessage());
}
?>