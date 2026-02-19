<?php
// ماژول مدیریت کاربران داشبورد
// Dashboard User Management Module
if (!defined('MASTER_SECRET')) { exit; }

// پردازش افزودن کاربر جدید
// Process adding new user
if (isset($_POST['act']) && $_POST['act'] == 'add_user') {
    $ph = htmlspecialchars($_POST['phone']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $uname = $ph; // به صورت پیش‌فرض شماره به عنوان نام کاربری در نظر گرفته می‌شود
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (phone, username, password, role, status) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$ph, $uname, $pass, $role]);
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #c3e6cb;'>✅ کاربر با موفقیت اضافه شد.</div>";
    } catch (PDOException $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #f5c6cb;'>❌ خطا: این شماره قبلاً ثبت شده است.</div>";
    }
}

// دریافت لیست تمام کاربران به همراه ادمین‌ها
// Fetch all users list including admins
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<div style="padding: 20px; font-family: Tahoma, 'Vazirmatn', sans-serif; direction: rtl; text-align: right;">
    <h2 style="margin-bottom: 20px; color: #333; font-size: 24px; border-bottom: 2px solid #ddd; padding-bottom: 10px;">مدیریت کاربران و دسترسی‌ها</h2>

    <div style="background: #fdfdfd; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; color: #555; margin-bottom: 15px;">➕ افزودن کاربر جدید</h3>
        <form method="POST" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
            <input type="hidden" name="act" value="add_user">
            
            <input type="text" name="phone" placeholder="شماره همراه (مثال: 09123456789)" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; flex: 1; min-width: 200px; outline: none;">
            
            <input type="password" name="password" placeholder="رمز عبور" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; flex: 1; min-width: 200px; outline: none;">
            
            <select name="role" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; flex: 1; min-width: 150px; outline: none; background: white;">
                <option value="user">کاربر عادی</option>
                <option value="admin">مدیر سیستم (Admin)</option>
            </select>
            
            <button type="submit" style="padding: 10px 25px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; min-width: 120px; transition: 0.3s;">ایجاد کاربر</button>
        </form>
    </div>

    <h3 style="color: #555; margin-bottom: 15px;">📋 لیست کاربران سیستم</h3>
    <div style="overflow-x: auto; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <table style="width: 100%; border-collapse: collapse; text-align: right;">
            <thead>
                <tr style="background: #f4f6f8; border-bottom: 2px solid #ddd;">
                    <th style="padding: 15px; color: #333;">شناسه (ID)</th>
                    <th style="padding: 15px; color: #333;">شماره همراه</th>
                    <th style="padding: 15px; color: #333;">نام کاربری</th>
                    <th style="padding: 15px; color: #333;">نقش (Role)</th>
                    <th style="padding: 15px; color: #333;">وضعیت</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($users) > 0): ?>
                    <?php foreach($users as $u): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px; font-weight: bold; color: #666;"><?= $u['id'] ?></td>
                        <td style="padding: 15px;"><?= htmlspecialchars($u['phone'] ?? '---') ?></td>
                        <td style="padding: 15px;"><?= htmlspecialchars($u['username'] ?? '---') ?></td>
                        <td style="padding: 15px;">
                            <?php if(isset($u['role']) && $u['role'] == 'admin'): ?>
                                <span style="background: #dc3545; color: white; padding: 5px 10px; border-radius: 12px; font-size: 13px;">مدیر</span>
                            <?php else: ?>
                                <span style="background: #6c757d; color: white; padding: 5px 10px; border-radius: 12px; font-size: 13px;">کاربر</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px;">
                            <?= (isset($u['status']) && $u['status'] == 1) ? '<span style="color: #28a745; font-weight: bold;">فعال</span>' : '<span style="color: #dc3545; font-weight: bold;">مسدود</span>' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="padding: 30px; text-align: center; color: #888;">هیچ کاربری در دیتابیس یافت نشد.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
