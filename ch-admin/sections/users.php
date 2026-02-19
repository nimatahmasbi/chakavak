<?php
// ماژول پیشرفته مدیریت کاربران
// Advanced User Management Module
if (!defined('MASTER_SECRET')) { exit; }

// پیدا کردن شناسه ادمین برای ارسال پیام
// Find admin ID for sending messages
$adminStmt = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1");
$adminId = $adminStmt->fetchColumn() ?: 1;

// --- پردازش عملیات فرم‌ها ---
// --- Process Form Actions ---
if (isset($_POST['act'])) {
    
    // افزودن کاربر
    // Add User
    if ($_POST['act'] == 'add_user') {
        $ph = htmlspecialchars($_POST['phone']);
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        try {
            $pdo->prepare("INSERT INTO users (phone, username, password, role, status) VALUES (?, ?, ?, ?, 1)")->execute([$ph, $ph, $pass, $role]);
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>✅ کاربر افزوده شد.</div>";
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>❌ خطا: شماره تکراری است.</div>";
        }
    }
    
    // تغییر وضعیت (مسدود/فعال)
    // Toggle Status (Block/Unblock)
    elseif ($_POST['act'] == 'toggle_status') {
        $uid = (int)$_POST['user_id'];
        $newStatus = (int)$_POST['new_status'];
        $pdo->prepare("UPDATE users SET status=? WHERE id=?")->execute([$newStatus, $uid]);
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>✅ وضعیت کاربر تغییر کرد.</div>";
    }

    // ارسال پیام شخصی به یک کاربر
    // Send Private Message to a User
    elseif ($_POST['act'] == 'send_pv') {
        $targetId = (int)$_POST['user_id'];
        $msg = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');
        $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (?, ?, 'pv', ?, NOW())")->execute([$adminId, $targetId, $msg]);
        echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>✉️ پیام شخصی با موفقیت ارسال شد.</div>";
    }

    // ارسال اعلان همگانی (به تمام کاربران)
    // Send Global Announcement (To all users)
    elseif ($_POST['act'] == 'send_all') {
        $msg = "📢 اعلان مدیریت:\n" . htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');
        $users = $pdo->query("SELECT id FROM users WHERE status=1")->fetchAll(PDO::FETCH_COLUMN);
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (?, ?, 'pv', ?, NOW())");
        foreach ($users as $u) {
            $stmt->execute([$adminId, $u, $msg]);
        }
        echo "<div style='background: #cce5ff; color: #004085; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>📢 اعلان به تمام کاربران فعال ارسال شد.</div>";
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<div style="padding: 20px; font-family: Tahoma, 'Vazirmatn', sans-serif; direction: rtl; text-align: right; background: #f9f9f9; min-height: 100vh;">
    
    <div style="background: white; padding: 20px; border: 1px solid #007bff; border-radius: 8px; margin-bottom: 30px; border-right: 5px solid #007bff;">
        <h3 style="margin-top: 0; color: #007bff;">📢 ارسال اعلان همگانی (کانال مدیریت)</h3>
        <p style="color: #666; font-size: 14px;">این پیام به صورت خودکار به پی‌وی تمام کاربران فعال سیستم ارسال می‌شود.</p>
        <form method="POST" style="display: flex; gap: 10px; align-items: flex-start;">
            <input type="hidden" name="act" value="send_all">
            <textarea name="message" required placeholder="متن اعلان خود را اینجا بنویسید..." style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; min-height: 80px; outline: none; resize: vertical;"></textarea>
            <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; height: 100%;">ارسال به همه</button>
        </form>
    </div>

    <div style="background: white; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 30px;">
        <h3 style="margin-top: 0; color: #555;">➕ افزودن کاربر جدید</h3>
        <form method="POST" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="hidden" name="act" value="add_user">
            <input type="text" name="phone" placeholder="شماره همراه" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; flex: 1; outline: none;">
            <input type="password" name="password" placeholder="رمز عبور" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; flex: 1; outline: none;">
            <select name="role" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; outline: none;">
                <option value="user">کاربر</option>
                <option value="admin">مدیر</option>
            </select>
            <button type="submit" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">ایجاد کاربر</button>
        </form>
    </div>

    <h3 style="color: #555;">📋 لیست و مدیریت کاربران</h3>
    <div style="overflow-x: auto; background: white; border-radius: 8px; border: 1px solid #e0e0e0;">
        <table style="width: 100%; border-collapse: collapse; text-align: right;">
            <thead style="background: #f4f6f8; border-bottom: 2px solid #ddd;">
                <tr>
                    <th style="padding: 12px; border-left: 1px solid #eee;">ID</th>
                    <th style="padding: 12px; border-left: 1px solid #eee;">شماره / نام</th>
                    <th style="padding: 12px; border-left: 1px solid #eee;">نقش</th>
                    <th style="padding: 12px; border-left: 1px solid #eee;">وضعیت</th>
                    <th style="padding: 12px;">عملیات (ارسال پیام / مسدودسازی)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; border-left: 1px solid #eee;"><?= $u['id'] ?></td>
                    <td style="padding: 12px; border-left: 1px solid #eee;">
                        <b><?= htmlspecialchars($u['phone']) ?></b><br>
                        <small style="color:#888;"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></small>
                    </td>
                    <td style="padding: 12px; border-left: 1px solid #eee;">
                        <?= $u['role'] == 'admin' ? '<span style="color:red;">مدیر</span>' : 'کاربر' ?>
                    </td>
                    <td style="padding: 12px; border-left: 1px solid #eee;">
                        <?= $u['status'] == 1 ? '<span style="color:green;">فعال</span>' : '<span style="color:red;">مسدود</span>' ?>
                    </td>
                    <td style="padding: 12px; display: flex; gap: 10px; flex-wrap: wrap;">
                        
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="act" value="toggle_status">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="new_status" value="<?= $u['status'] == 1 ? 0 : 1 ?>">
                            <?php if($u['status'] == 1): ?>
                                <button type="submit" style="background:#dc3545; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">مسدود سازی</button>
                            <?php else: ?>
                                <button type="submit" style="background:#28a745; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">آزادسازی (فعال)</button>
                            <?php endif; ?>
                        </form>

                        <form method="POST" style="margin: 0; display:flex; gap:5px;">
                            <input type="hidden" name="act" value="send_pv">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="text" name="message" placeholder="متن پیام شخصی..." required style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; width:150px;">
                            <button type="submit" style="background:#17a2b8; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">ارسال پیام</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
