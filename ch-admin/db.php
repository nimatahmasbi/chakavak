<?php
// پیکربندی پایگاه داده
// Database Configuration
if(!defined('MASTER_SECRET')) {
    // کلید امنیتی یکپارچه سیستم
    // Unified System Security Key
    define('MASTER_SECRET', 'CH-k8#vA!z9$Pq2R_92mN@LpX5*uT1&Z_sQ7wE4yB9vC3xM6jK8nI0oP-v2026_SECURE');
}
// مشخصات اتصال به دیتابیس
// Database Connection Credentials
$db_host = 'localhost'; 
$db_name = 'sir_1'; 
$db_user = 'sir_1'; 
$db_pass = 'HLLbqrCOA!+r-NK5'; 

try {
    // ایجاد اتصال ایمن با دیتابیس
    // Create secure database connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // توقف اجرای برنامه در صورت خطای اتصال
    // Terminate execution on connection error
    die("Connection Failed: " . $e->getMessage());
}
?>