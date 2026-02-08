<?php
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Access Denied']);
    exit;
}

$act = $_POST['act'] ?? $_POST['action'] ?? '';

// --------------------------------------------------------------------------
// 1. تغییر وضعیت کاربر (آزاد/مسدود)
// --------------------------------------------------------------------------
if ($act == 'admin_toggle_user') {
    $uid = $_POST['user_id'];
    
    // دریافت وضعیت فعلی
    $curr = $pdo->prepare("SELECT is_approved, first_name FROM users WHERE id=?");
    $curr->execute([$uid]);
    $u = $curr->fetch();
    
    if (!$u) { echo json_encode(['status'=>'error']); exit; }
    
    $newState = ($u['is_approved'] == 1) ? 0 : 1;
    
    // آپدیت وضعیت
    $pdo->prepare("UPDATE users SET is_approved = ? WHERE id = ?")->execute([$newState, $uid]);
    
    // ارسال پیام اطلاع‌رسانی
    $msg = "";
    if ($newState == 1) {
        $msg = "تبریک! حساب کاربری شما توسط مدیریت فعال شد. هم‌اکنون می‌توانید از تمامی امکانات استفاده کنید.";
    } else {
        $msg = "توجه: حساب کاربری شما توسط مدیریت مسدود یا محدود شد. در این وضعیت تنها می‌توانید با پشتیبانی در ارتباط باشید.";
        // اگر مسدود شد، توکن‌ها را پاک کن تا از اپ بیرون بیفتد (اختیاری)
        // $pdo->prepare("DELETE FROM user_tokens WHERE user_id = ?")->execute([$uid]); 
    }
    
    $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (1, ?, 'dm', ?, NOW())")
        ->execute([$uid, $msg]);
    
    echo json_encode(['status' => 'ok', 'new_state' => $newState]);
}

// --------------------------------------------------------------------------
// 2. دریافت لیست‌ها
// --------------------------------------------------------------------------
elseif ($act == 'admin_get_lists') {
    $type = $_POST['list_type'] ?? 'users';
    $stats = [
        'users'  => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'groups' => $pdo->query("SELECT COUNT(*) FROM groups")->fetchColumn(),
        'msgs'   => $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn()
    ];

    if ($type == 'users') {
        $list = $pdo->query("SELECT id, username, first_name, last_name, phone, is_approved, created_at FROM users ORDER BY id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $list = $pdo->query("SELECT g.id, g.name, g.type, g.is_banned, g.created_at, (SELECT COUNT(*) FROM group_members WHERE group_id=g.id) as member_count FROM groups g ORDER BY g.id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
        foreach($list as &$g) { $g['first_name'] = $g['member_count'] . ' عضو'; $g['creator_id']=0; }
    }
    echo json_encode(['status' => 'ok', 'list' => $list, 'stats' => $stats]);
}

// --------------------------------------------------------------------------
// 3. سایر عملیات ادمین (ویرایش، حذف، تنظیمات)
// --------------------------------------------------------------------------
elseif ($act == 'admin_ban_group') {
    $gid = $_POST['group_id'];
    $pdo->prepare("UPDATE groups SET is_banned = NOT is_banned WHERE id = ?")->execute([$gid]);
    echo json_encode(['status' => 'ok']);
}
elseif ($act == 'admin_get_settings') {
    $s = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    echo json_encode(['status' => 'ok', 'data' => $s ?: []]);
}
elseif ($act == 'admin_save_settings') {
    $stmt = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");
    foreach(['ippanel_key', 'ippanel_line', 'enable_2fa', 'enable_passkey'] as $k) $stmt->execute([$k, $_POST[$k]??'']);
    echo json_encode(['status' => 'ok']);
}
elseif ($act == 'admin_delete_group') {
    $g = $_POST['group_id'];
    $pdo->exec("DELETE FROM groups WHERE id=$g");
    $pdo->exec("DELETE FROM group_members WHERE group_id=$g");
    $pdo->exec("DELETE FROM messages WHERE target_id=$g AND type IN ('group','channel')");
    echo json_encode(['status'=>'ok']);
}
elseif ($act == 'admin_get_dm_history') {
    // چت با ادمین
    $tid = $_POST['target_id']; // ID کاربر
    $msgs = $pdo->prepare("SELECT * FROM messages WHERE (sender_id=1 AND target_id=?) OR (sender_id=? AND target_id=1) ORDER BY created_at ASC");
    $msgs->execute([$tid, $tid]);
    echo json_encode(['status'=>'ok', 'list'=>$msgs->fetchAll(PDO::FETCH_ASSOC), 'chat_key'=>'']);
}
elseif ($act == 'admin_send_dm') {
    $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (1, ?, 'dm', ?, NOW())")
        ->execute([$_POST['target_id'], $_POST['message']]);
    echo json_encode(['status'=>'ok']);
}
else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid Action']);
}
?>