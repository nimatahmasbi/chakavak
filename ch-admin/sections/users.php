<?php
/**
 * English|Persian: Admin User Management | مدیریت کاربران توسط ادمین
 */
if (!defined('MASTER_SECRET')) { exit('Access Denied'); }

// عملیات افزودن کاربر - Add User Action
if (isset($_POST['act']) && $_POST['act'] == 'add_user') {
    $phone|شماره = htmlspecialchars($_POST['phone']);
    $username|نام_کاربری = htmlspecialchars($_POST['username']);
    $password|رمز = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role|نقش = $_POST['role'];

    $stmt = $pdo->prepare("INSERT INTO users (phone, username, password, role, status) VALUES (?, ?, ?, ?, 1)");
    if ($stmt->execute([$phone|شماره, $username|نام_کاربری, $password|رمز, $role|نقش])) {
        echo "<script>alert('کاربر با موفقیت افزوده شد');</script>";
    }
}

// عملیات بروزرسانی تنظیمات - Update Settings Action
if (isset($_POST['act']) && $_POST['act'] == 'save_configs') {
    foreach ($_POST['cfg'] as $key => $val) {
        $pdo->prepare("UPDATE settings SET s_value = ? WHERE s_key = ?")->execute([$val, $key]);
    }
    echo "<script>alert('تنظیمات ذخیره شد');</script>";
}

// دریافت اطلاعات فعلی
$configs = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div style="direction:rtl; padding:20px; font-family:tahoma;">
    <h3>تنظیمات امنیتی و سیستمی | System & Security Settings</h3>
    <form method="POST" style="background:#f4f4f4; padding:15px; border-radius:8px;">
        <input type="hidden" name="act" value="save_configs">
        <div style="margin-bottom:10px;">
            <label>وضعیت ارسال پیامک:</label>
            <select name="cfg[sms_active]">
                <option value="1" <?= $configs['sms_active'] == '1' ? 'selected' : '' ?>>فعال (ارسال واقعی)</option>
                <option value="0" <?= $configs['sms_active'] == '0' ? 'selected' : '' ?>>غیرفعال (فقط نمایش کد تایید)</option>
            </select>
        </div>
        <div style="margin-bottom:10px;">
            <label>اجازه ثبت‌نام جدید:</label>
            <select name="cfg[registration_enabled]">
                <option value="1" <?= $configs['registration_enabled'] == '1' ? 'selected' : '' ?>>فعال</option>
                <option value="0" <?= $configs['registration_enabled'] == '0' ? 'selected' : '' ?>>غیرفعال</option>
            </select>
        </div>
        <button type="submit" style="background:blue; color:white; padding:8px 15px; border:none; border-radius:4px; cursor:pointer;">ذخیره تنظیمات | Save</button>
    </form>

    <hr style="margin:30px 0;">

    <h3>افزودن کاربر جدید | Add New User</h3>
    <form method="POST" style="background:#e9ecef; padding:15px; border-radius:8px;">
        <input type="hidden" name="act" value="add_user">
        <input type="text" name="phone" placeholder="شماره همراه | Phone" required style="padding:5px; margin:5px;">
        <input type="text" name="username" placeholder="نام کاربری | Username" required style="padding:5px; margin:5px;">
        <input type="password" name="password" placeholder="رمز عبور | Password" required style="padding:5px; margin:5px;">
        <select name="role" style="padding:5px; margin:5px;">
            <option value="user">کاربر عادی | User</option>
            <option value="admin">مدیر سیستم | Admin</option>
        </select>
        <button type="submit" style="background:green; color:white; padding:8px 15px; border:none; border-radius:4px; cursor:pointer;">ایجاد کاربر | Create</button>
    </form>
</div>
