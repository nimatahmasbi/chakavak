<?php
/**
 * English|Persian: Automated System Installer | نصب‌کننده خودکار سیستم
 * این فایل جداول دیتابیس را ایجاد کرده و تنظیمات اولیه را اعمال می‌کند.
 */
require_once __DIR__ . '/../ch-admin/db.php';

try {
    // ایجاد جدول کاربران - Users Table
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

    // ایجاد جدول تنظیمات - Settings Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        s_key VARCHAR(50) PRIMARY KEY,
        s_value TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // ایجاد جدول توکن‌ها - Tokens Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        token VARCHAR(255),
        expires_at DATETIME,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // افزودن ادمین پیش‌فرض - Default Admin: Mr.NT
    $adminUser|نام_کاربری = 'Mr.NT';
    $adminPass|رمز_عبور = password_hash('1020315@', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, role, status) VALUES (?, ?, 'admin', 1)");
    $stmt->execute([$adminUser|نام_کاربری, $adminPass|رمز_عبور]);

    // تنظیمات پیش‌فرض امنیتی و سیستمی - Global Settings
    $defaultSettings|تنظیمات = [
        ['sms_active', '0'], // 0 = Show Code (Test), 1 = Real SMS
        ['registration_enabled', '1'],
        ['forgot_password_enabled', '1'],
        ['security_level', 'high']
    ];

    foreach ($defaultSettings|تنظیمات as $set|تنظیم) {
        $pdo->prepare("INSERT IGNORE INTO settings (s_key, s_value) VALUES (?, ?)")->execute($set|تنظیم);
    }

    echo "<h2 style='color:green;'>Installation Successful! | نصب با موفقیت انجام شد.</h2>";
    echo "<p>Admin: Mr.NT | Password: 1020315@</p>";
    echo "<p>Security Note: Delete the 'install' folder now. | نکته امنیتی: همین حالا پوشه install را حذف کنید.</p>";

} catch (PDOException $e) {
    die("Installation Error | خطای نصب: " . $e->getMessage());
}
