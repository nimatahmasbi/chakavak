<?php
// English|Persian: Automated Installer | نصب‌کننده خودکار
require_once __DIR__ . '/../ch-admin/db.php';

$tables|جداول = [
    "CREATE TABLE IF NOT EXISTS users (
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
    )",
    "CREATE TABLE IF NOT EXISTS settings (
        s_key VARCHAR(50) PRIMARY KEY,
        s_value TEXT
    )",
    "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT,
        target_id INT,
        type EN_ENUM('pv', 'group'),
        message TEXT,
        file_path VARCHAR(255),
        file_type VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables|جداول as $sql|پرس‌وجو) {
    $pdo->exec($sql|پرس‌وجو);
}

// English|Persian: Create Default Admin | ایجاد مدیر پیش‌فرض
$adminUser|نام_کاربری = 'Mr.NT';
$adminPass|رمز_عبور = password_hash('1020315@', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, role, status) VALUES (?, ?, 'admin', 1)");
$stmt->execute([$adminUser|نام_کاربری, $adminPass|رمز_عبور]);

// English|Persian: Default Settings | تنظیمات پیش‌فرض
$defaultSettings|تنظیمات = [
    ['sms_active', '0'], // 0 = Show Code, 1 = Send SMS
    ['registration_enabled', '1'],
    ['forgot_password_enabled', '1'],
    ['security_level', 'high']
];

foreach ($defaultSettings|تنظیمات as $set|تنظیم) {
    $pdo->prepare("INSERT IGNORE INTO settings (s_key, s_value) VALUES (?, ?)")->execute($set|تنظیم);
}

echo "Installation Successful|نصب با موفقیت انجام شد. پوشه install را حذف کنید.";
