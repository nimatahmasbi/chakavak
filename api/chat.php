<?php
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

$act = $_POST['act'] ?? $_POST['action'] ?? '';
$uid = $_SESSION['uid'] ?? 0;

if (!$uid) exit(json_encode(['status'=>'error', 'msg'=>'Auth error']));

$me = $pdo->prepare("SELECT is_approved FROM users WHERE id=?");
$me->execute([$uid]);
$is_approved = $me->fetchColumn();

// --- لیست چت‌ها (فیلتر گروه‌های حذف شده) ---
if ($act == 'get_chats_list') {
    $sql = "
    SELECT list.*, m.message as last_msg, m.created_at as last_time
    FROM (
        -- گروه‌ها: فقط آنهایی که is_deleted=0 هستند
        SELECT g.id, g.type, g.name, g.avatar, g.chat_key, 
               (SELECT COUNT(*) FROM messages WHERE target_id=g.id AND type=g.type AND is_read=0 AND sender_id != $uid) as unread
        FROM groups g JOIN group_members gm ON g.id = gm.group_id 
        WHERE gm.user_id = ? AND g.is_deleted = 0
        
        UNION ALL
        
        -- دایرکت‌ها
        SELECT u.id, 'dm' as type, CONCAT(u.first_name,' ',u.last_name) as name, u.avatar, '' as chat_key,
               (SELECT COUNT(*) FROM messages WHERE target_id=$uid AND sender_id=u.id AND type='dm' AND is_read=0) as unread
        FROM users u JOIN user_contacts uc ON u.id = uc.contact_id 
        WHERE uc.owner_id = ? AND u.id != 1
        
        UNION ALL
        
        -- پشتیبانی
        SELECT 1 as id, 'dm' as type, 'پشتیبانی (ادمین)' as name, 'assets/img/chakavak.png' as avatar, '' as chat_key,
               (SELECT COUNT(*) FROM messages WHERE sender_id=1 AND target_id=$uid AND type='dm' AND is_read=0) as unread
    ) as list
    LEFT JOIN messages m ON m.id = (
        SELECT id FROM messages WHERE (list.type IN ('group', 'channel') AND target_id=list.id AND type=list.type)
        OR (list.type = 'dm' AND ((sender_id=$uid AND target_id=list.id) OR (sender_id=list.id AND target_id=$uid)))
        ORDER BY id DESC LIMIT 1
    )
    WHERE (list.type IN ('group','channel')) OR (list.id = 1) OR (m.id IS NOT NULL) OR (list.type='dm')
    ORDER BY last_time DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$uid, $uid]);
    echo json_encode(['status'=>'ok', 'list'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

// --- دریافت پیام‌ها ---
elseif ($act == 'get_messages') {
    $tid = $_POST['target_id'];
    $type = $_POST['type'];
    $key = ""; 
    $can_write = true;
    
    if ($is_approved == 0 && ($type != 'dm' || $tid != 1)) $can_write = false;
    
    if($type == 'group' || $type == 'channel') {
        // چک کردن عضویت و حذف نشدن گروه
        $check = $pdo->prepare("SELECT 1 FROM group_members gm JOIN groups g ON g.id=gm.group_id WHERE gm.group_id=? AND gm.user_id=? AND g.is_deleted=0");
        $check->execute([$tid, $uid]);
        if(!$check->fetch()) exit(json_encode(['status'=>'error', 'msg'=>'دسترسی ندارید یا گروه حذف شده است']));
        
        $key = $pdo->query("SELECT chat_key FROM groups WHERE id=$tid")->fetchColumn() ?: '';
    }

    $sql = "SELECT m.*, u.first_name, u.avatar FROM messages m LEFT JOIN users u ON m.sender_id = u.id WHERE m.type = ?";
    $params = [$type];

    if ($type == 'dm') {
        $sql .= " AND ( (m.sender_id=? AND m.target_id=?) OR (m.sender_id=? AND m.target_id=?) )";
        $params[] = $uid; $params[] = $tid; $params[] = $tid; $params[] = $uid;
    } else {
        $sql .= " AND m.target_id=?";
        $params[] = $tid;
    }
    
    $sql .= " ORDER BY m.id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    if ($type == 'dm') $pdo->prepare("UPDATE messages SET is_read=1 WHERE target_id=? AND sender_id=? AND type='dm'")->execute([$uid, $tid]);
    else $pdo->prepare("UPDATE messages SET is_read=1 WHERE target_id=? AND type=? AND sender_id!=?")->execute([$tid, $type, $uid]);

    echo json_encode(['status'=>'ok', 'list'=>$stmt->fetchAll(PDO::FETCH_ASSOC), 'chat_key'=>$key, 'can_write'=>$can_write, 'header'=>['status'=>($type=='dm'?'online':'')]]);
}

// --- حذف دایرکت (مخاطب) ---
elseif ($act == 'delete_direct') {
    $tid = $_POST['target_id'];
    // حذف از لیست مخاطبین من (پیام‌ها در دیتابیس می‌مانند تا ادمین ببیند)
    $pdo->prepare("DELETE FROM user_contacts WHERE owner_id=? AND contact_id=?")->execute([$uid, $tid]);
    echo json_encode(['status'=>'ok']);
}

// --- ارسال پیام ---
elseif ($act == 'send_message') {
    $tid = $_POST['target_id']; $type = $_POST['type']; $msg = $_POST['message']; $is_img = $_POST['is_image'] ?? 0;

    if ($is_approved == 0 && ($type != 'dm' || $tid != 1)) exit(json_encode(['status'=>'error']));

    $file_path = null;
    if (!empty($_FILES['file']['name'])) {
        $dir = '../uploads/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $name = uniqid() . '_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $dir . $name)) $file_path = 'uploads/' . $name;
    }
    
    $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, file_path, file_type, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())")
        ->execute([$uid, $tid, $type, $msg, $file_path, ($is_img==1?'image':($is_img==2?'voice':'file'))]);
    echo json_encode(['status'=>'ok']);
}
?>