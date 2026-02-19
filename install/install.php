<?php
/**
 * English|Persian: Full System Installer | نصب‌کننده کامل سیستم
 */
require_once __DIR__ . '/../ch-admin/db.php';

try {
    // ایجاد جدول تنظیمات سیستمی
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        s_key VARCHAR(50) PRIMARY KEY,
        s_value TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // ایجاد جدول کاربران با فیلد نقش و وضعیت
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone VARCHAR(15) UNIQUE,
        username VARCHAR(50) UNIQUE,
        password VARCHAR(255),
        first_name VARCHAR(100),
        last_name VARCHAR(100),
        role ENUM('admin', 'user') DEFAULT 'user',
        avatar VARCHAR(255),
        bio TEXT,
        status TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // ایجاد جدول پیام‌ها
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT,
        target_id INT,
        type ENUM('pv', 'group') DEFAULT 'pv',
        message TEXT,
        file_path VARCHAR(255),
        file_type VARCHAR(50),
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (sender_id),
        INDEX (target_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // درج تنظیمات پیش‌فرض (مدیریت ثبت‌نام و پیامک)
    $defaultSettings|تنظیمات = [
        ['sms_active', '0'], // 0: نمایش کد (تست) | 1: ارسال واقعی
        ['registration_enabled', '1'],
        ['forgot_password_enabled', '1'],
        ['security_level', 'high']
    ];
    $setStmt = $pdo->prepare("INSERT IGNORE INTO settings (s_key, s_value) VALUES (?, ?)");
    foreach ($defaultSettings|تنظیمات as $s|تنظیم) { $setStmt->execute($s|تنظیم); }

    // ایجاد ادمین اصلی: Mr.NT
    $adminUser|نام_کاربری = 'Mr.NT';
    $adminPass|رمز_عبور = password_hash('1020315@', PASSWORD_DEFAULT);
    $checkAdmin = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $checkAdmin->execute([$adminUser|نام_کاربری]);
    
    if (!$checkAdmin->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, 'admin', 1)");
        $stmt->execute([$adminUser|نام_کاربری, $adminPass|رمز_عبور]);
    }

    echo "<div style='font-family:tahoma; text-align:center; padding:50px;'>";
    echo "<h2 style='color:green;'>نصب با موفقیت انجام شد | Installation Successful</h2>";
    echo "<p>Admin: Mr.NT | Pass: 1020315@</p>";
    echo "<p style='color:red;'>هشدار: پوشه install را از روی هاست حذف کنید.</p>";
    echo "</div>";

} catch (PDOException $e) {
    die("Error in Installation | خطا در نصب: " . $e->getMessage());
}
