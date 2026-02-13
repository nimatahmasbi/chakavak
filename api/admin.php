<?php
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) exit(json_encode(['status'=>'error', 'msg'=>'Admin Auth Required']));

$act = $_POST['act'] ?? '';

// --- دریافت لیست‌ها ---
if ($act == 'admin_get_lists') {
    $users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $groups = $pdo->query("SELECT * FROM groups ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    echo json_encode(['status'=>'ok', 'users'=>$users, 'groups'=>$groups, 'settings'=>$settings]);
}

// --- تغییر وضعیت کاربر + پیام سیستمی ---
elseif ($act == 'admin_toggle_user') {
    $uid = $_POST['user_id'];
    $state = $_POST['state']; 
    
    $pdo->prepare("UPDATE users SET is_approved=? WHERE id=?")->execute([$state, $uid]);
    
    $msg = ($state == 1) 
        ? "حساب شما فعال شد. اکنون می‌توانید از تمام امکانات استفاده کنید." 
        : "حساب شما مسدود شد. دسترسی‌های شما محدود گردید.";
        
    $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (1, ?, 'dm', ?, NOW())")->execute([$uid, $msg]);
    echo json_encode(['status'=>'ok']);
}

// --- دریافت پیام‌های گروه ---
elseif ($act == 'admin_get_group_msgs') {
    $gid = $_POST['group_id'];
    $stmt = $pdo->prepare("SELECT m.*, u.first_name FROM messages m LEFT JOIN users u ON m.sender_id=u.id WHERE target_id=? AND (type='group' OR type='channel') ORDER BY id DESC LIMIT 50");
    $stmt->execute([$gid]);
    echo json_encode(['status'=>'ok', 'list'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

// --- ارسال پیام به مدیر گروه ---
elseif ($act == 'admin_send_to_owner') {
    $gid = $_POST['group_id'];
    $msg = $_POST['message'];
    $oid = $pdo->prepare("SELECT user_id FROM group_members WHERE group_id=? AND role='admin' LIMIT 1");
    $oid->execute([$gid]);
    $adminId = $oid->fetchColumn();
    
    if($adminId) {
        $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (1, ?, 'dm', ?, NOW())")->execute([$adminId, $msg]);
        echo json_encode(['status'=>'ok']);
    } else {
        echo json_encode(['status'=>'error', 'msg'=>'مدیر یافت نشد']);
    }
}

// --- دریافت لیست چت‌های کاربر ---
elseif ($act == 'admin_get_user_chats_list') {
    $uid = $_POST['user_id'];
    $sql = "SELECT DISTINCT u.id, u.first_name, u.last_name FROM messages m JOIN users u ON (m.sender_id=u.id OR m.target_id=u.id) WHERE (m.sender_id=? OR m.target_id=?) AND m.type='dm' AND u.id != ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$uid, $uid, $uid]);
    echo json_encode(['status'=>'ok', 'list'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

// --- دریافت چت بین دو نفر ---
elseif ($act == 'admin_get_dm_history') {
    $u1 = $_POST['user1'];
    $u2 = $_POST['user2'];
    $sql = "SELECT m.*, u.first_name FROM messages m LEFT JOIN users u ON m.sender_id=u.id WHERE type='dm' AND ( (sender_id=? AND target_id=?) OR (sender_id=? AND target_id=?) ) ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$u1, $u2, $u2, $u1]);
    echo json_encode(['status'=>'ok', 'list'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

// --- ذخیره تنظیمات ---
elseif ($act == 'admin_save_settings') {
    $pdo->prepare("UPDATE settings SET value=? WHERE `key`='enable_2fa'")->execute([$_POST['s_2fa']]);
    $pdo->prepare("UPDATE settings SET value=? WHERE `key`='enable_passkey'")->execute([$_POST['s_pass']]);
    echo json_encode(['status'=>'ok']);
}

// --- حذف گروه ---
elseif ($act == 'admin_delete_group') {
    $gid = $_POST['group_id'];
    $pdo->prepare("DELETE FROM groups WHERE id=?")->execute([$gid]);
    $pdo->prepare("DELETE FROM group_members WHERE group_id=?")->execute([$gid]);
    $pdo->prepare("DELETE FROM messages WHERE target_id=? AND (type='group' OR type='channel')")->execute([$gid]);
    echo json_encode(['status'=>'ok']);
}

// --- حذف پیام ---
elseif ($act == 'admin_delete_msg') {
    $mid = $_POST['msg_id'];
    $pdo->prepare("DELETE FROM messages WHERE id=?")->execute([$mid]);
    echo json_encode(['status'=>'ok']);
}
?>