<?php
// اتصال به فایل کانفیگ (مسیر نسبی به پوشه ch-admin)
require_once __DIR__ . '/../ch-admin/config.php';

header('Content-Type: application/json');

// بررسی لاگین بودن ادمین
if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Admin Auth Required']);
    exit;
}

$act = $_POST['act'] ?? '';

// ------------------------------------------------------------------
// 1. دریافت لیست کلی (کاربران، گروه‌ها، تنظیمات) برای داشبورد
// ------------------------------------------------------------------
if ($act == 'admin_get_lists') {
    try {
        // دریافت تمام کاربران
        $users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        
        // دریافت تمام گروه‌ها و کانال‌ها
        $groups = $pdo->query("SELECT * FROM groups ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        
        // دریافت تنظیمات سیستم
        $settings = [];
        try {
            $settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) {
            // اگر جدول تنظیمات وجود نداشت، آرایه خالی برمی‌گردد
            $settings = [];
        }
        
        echo json_encode([
            'status' => 'ok', 
            'users' => $users ?: [], 
            'groups' => $groups ?: [], 
            'settings' => $settings
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
    }
}

// ------------------------------------------------------------------
// 2. تغییر وضعیت کاربر (فعال/مسدود) + ارسال پیام سیستمی
// ------------------------------------------------------------------
elseif ($act == 'admin_toggle_user') {
    $uid = $_POST['user_id'];
    $state = $_POST['state']; // 1 = فعال, 0 = مسدود
    
    // آپدیت وضعیت در دیتابیس
    $pdo->prepare("UPDATE users SET is_approved=? WHERE id=?")->execute([$state, $uid]);
    
    // تعیین متن پیام بر اساس وضعیت
    $msg = ($state == 1) 
        ? "حساب کاربری شما توسط مدیریت فعال شد. اکنون می‌توانید از تمام امکانات سامانه استفاده کنید." 
        : "حساب کاربری شما توسط مدیریت مسدود شد. دسترسی‌های شما محدود گردید.";
        
    // ارسال پیام از طرف سیستم (شناسه 1) به کاربر
    $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (1, ?, 'dm', ?, NOW())")
        ->execute([$uid, $msg]);
    
    echo json_encode(['status' => 'ok']);
}

// ------------------------------------------------------------------
// 3. دریافت لیست افرادی که یک کاربر خاص با آن‌ها چت کرده است
// ------------------------------------------------------------------
elseif ($act == 'admin_get_user_chats_list') {
    $uid = $_POST['user_id'];
    
    // کوئری برای پیدا کردن مخاطبین چت (چه فرستنده باشد چه گیرنده)
    $sql = "SELECT DISTINCT u.id, u.first_name, u.last_name 
            FROM messages m 
            JOIN users u ON (m.sender_id = u.id OR m.target_id = u.id)
            WHERE (m.sender_id = ? OR m.target_id = ?) 
            AND m.type = 'dm' 
            AND u.id != ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$uid, $uid, $uid]);
    
    echo json_encode(['status' => 'ok', 'list' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

// ------------------------------------------------------------------
// 4. دریافت متن کامل چت بین دو کاربر خاص
// ------------------------------------------------------------------
elseif ($act == 'admin_get_dm_history') {
    $u1 = $_POST['user1'];
    $u2 = $_POST['user2'];
    
    $sql = "SELECT m.*, u.first_name FROM messages m 
            LEFT JOIN users u ON m.sender_id = u.id 
            WHERE type = 'dm' 
            AND ( (sender_id = ? AND target_id = ?) OR (sender_id = ? AND target_id = ?) ) 
            ORDER BY id ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$u1, $u2, $u2, $u1]);
    
    echo json_encode(['status' => 'ok', 'list' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

// ------------------------------------------------------------------
// 5. دریافت پیام‌های داخل یک گروه یا کانال (برای نظارت)
// ------------------------------------------------------------------
elseif ($act == 'admin_get_group_msgs') {
    $gid = $_POST['group_id'];
    
    $stmt = $pdo->prepare("SELECT m.*, u.first_name, u.last_name 
                           FROM messages m 
                           LEFT JOIN users u ON m.sender_id = u.id 
                           WHERE target_id = ? 
                           AND (type = 'group' OR type = 'channel') 
                           ORDER BY id DESC LIMIT 50");
    $stmt->execute([$gid]);
    
    echo json_encode(['status' => 'ok', 'list' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

// ------------------------------------------------------------------
// 6. ارسال پیام مستقیم به مدیر (سازنده) یک گروه
// ------------------------------------------------------------------
elseif ($act == 'admin_send_to_owner') {
    $gid = $_POST['group_id'];
    $msg = $_POST['message'];
    
    // پیدا کردن شناسه مدیر گروه
    $owner = $pdo->prepare("SELECT user_id FROM group_members WHERE group_id = ? AND role = 'admin' LIMIT 1");
    $owner->execute([$gid]);
    $adminId = $owner->fetchColumn();
    
    if ($adminId) {
        $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (1, ?, 'dm', ?, NOW())")
            ->execute([$adminId, $msg]);
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'مدیر گروه یافت نشد']);
    }
}

// ------------------------------------------------------------------
// 7. ذخیره تنظیمات سیستم (مثل فعال/غیرفعال کردن 2FA)
// ------------------------------------------------------------------
elseif ($act == 'admin_save_settings') {
    if (isset($_POST['s_2fa'])) {
        $pdo->prepare("UPDATE settings SET value = ? WHERE `key` = 'enable_2fa'")->execute([$_POST['s_2fa']]);
    }
    if (isset($_POST['s_pass'])) {
        $pdo->prepare("UPDATE settings SET value = ? WHERE `key` = 'enable_passkey'")->execute([$_POST['s_pass']]);
    }
    echo json_encode(['status' => 'ok']);
}

// ------------------------------------------------------------------
// 8. حذف کامل گروه/کانال
// ------------------------------------------------------------------
elseif ($act == 'admin_delete_group') {
    $gid = $_POST['group_id'];
    
    // حذف گروه از جدول گروه‌ها
    $pdo->prepare("DELETE FROM groups WHERE id = ?")->execute([$gid]);
    
    // حذف اعضای گروه
    $pdo->prepare("DELETE FROM group_members WHERE group_id = ?")->execute([$gid]);
    
    // حذف تمام پیام‌های گروه
    $pdo->prepare("DELETE FROM messages WHERE target_id = ? AND (type = 'group' OR type = 'channel')")->execute([$gid]);
    
    echo json_encode(['status' => 'ok']);
}

// ------------------------------------------------------------------
// 9. حذف یک پیام خاص توسط ادمین
// ------------------------------------------------------------------
elseif ($act == 'admin_delete_msg') {
    $mid = $_POST['msg_id'];
    $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([$mid]);
    echo json_encode(['status' => 'ok']);
}

else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid Action']);
}
?>