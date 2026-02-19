<?php
/**
 * English|Persian: User & System Management | مدیریت کاربران و سیستم
 */
if (!defined('MASTER_SECRET')) { exit; }

// پردازش افزودن کاربر جدید - Process Add User
if (isset($_POST['act']) && $_POST['act'] == 'add_new_user') {
    $phone|شماره = htmlspecialchars($_POST['phone']);
    $username|نام_کاربری = htmlspecialchars($_POST['username']);
    $role|نقش = $_POST['role'];
    $password|رمز = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (phone, username, password, role) VALUES (?, ?, ?)");
    if ($stmt->execute([$phone|شماره, $username|نام_کاربری, $password|رمز, $role|نقش])) {
        echo "<div class='alert alert-success'>User Created Successfully | کاربر با موفقیت ساخته شد.</div>";
    }
}

// پردازش تنظیمات سیستمی - Process System Settings
if (isset($_POST['act']) && $_POST['act'] == 'update_settings') {
    foreach ($_POST['s'] as $key => $value) {
        $pdo->prepare("UPDATE settings SET s_value = ? WHERE s_key = ?")->execute([$value, $key]);
    }
    echo "<div class='alert alert-info'>Settings Updated | تنظیمات بروزرسانی شد.</div>";
}

// دریافت تنظیمات فعلی
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="admin-container">
    <div class="card mb-4">
        <h3>System Config | تنظیمات سیستمی</h3>
        <form method="POST">
            <input type="hidden" name="act" value="update_settings">
            <label>SMS Status | وضعیت پیامک:</label>
            <select name="s[sms_active]">
                <option value="1" <?= $settings['sms_active'] == '1' ? 'selected' : '' ?>>Active (Real Send) | فعال (ارسال واقعی)</option>
                <option value="0" <?= $settings['sms_active'] == '0' ? 'selected' : '' ?>>Inactive (Show Code) | غیرفعال (نمایش کد)</option>
            </select>
            
            <label>Registration | ثبت‌نام کاربر جدید:</label>
            <select name="s[registration_enabled]">
                <option value="1" <?= $settings['registration_enabled'] == '1' ? 'selected' : '' ?>>Enabled | فعال</option>
                <option value="0" <?= $settings['registration_enabled'] == '0' ? 'selected' : '' ?>>Disabled | غیرفعال</option>
            </select>

            <button type="submit" class="btn-save">Save Settings | ذخیره تنظیمات</button>
        </form>
    </div>

    <div class="card">
        <h3>Add User | افزودن کاربر جدید</h3>
        <form method="POST">
            <input type="hidden" name="act" value="add_new_user">
            <input type="text" name="phone" placeholder="Phone | شماره همراه" required>
            <input type="text" name="username" placeholder="Username | نام کاربری" required>
            <input type="password" name="password" placeholder="Password | رمز عبور" required>
            <select name="role">
                <option value="user">User | کاربر عادی</option>
                <option value="admin">Admin | مدیر</option>
            </select>
            <button type="submit" class="btn-add">Create User | ایجاد کاربر</button>
        </form>
    </div>
</div>
