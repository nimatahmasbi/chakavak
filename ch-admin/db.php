
<?php
/**
 * English|Persian: Database Configuration | پیکربندی دیتابیس
 */
if(!defined('MASTER_SECRET')) {
    define('MASTER_SECRET', 'CH-k8#vA!z9$Pq2R_92mN@LpX5*uT1&Z_sQ7wE4yB9vC3xM6jK8nI0oP-v2026_SECURE');
}

$db_host = 'localhost';
$db_name = 'sir_1'; // نام دیتابیس شما
$db_user = 'sir_1'; // نام کاربری دیتابیس
$db_pass = 'HLLbqrCOA!+r-NK5'; // رمز عبور دیتابیس

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection Failed|خطا در اتصال: " . $e->getMessage());
}
?>
