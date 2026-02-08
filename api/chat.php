<?php
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

$act = $_POST['act'] ?? $_POST['action'] ?? '';
$uid = $_SESSION['uid'] ?? 0;

if (!$uid) exit(json_encode(['status'=>'error', 'msg'=>'Auth error']));

// دریافت وضعیت کاربر جاری (برای چک کردن مسدودی)
$me = $pdo->prepare("SELECT is_approved FROM users WHERE id=?");
$me->execute([$uid]);
$is_approved = $me->fetchColumn();

// --- ارسال پیام ---
if ($act == 'send_message') {
    $tid = $_POST['target_id'];
    $type = $_POST['type'];
    $msg = $_POST['message'];
    $is_img = $_POST['is_image'] ?? 0;
    
    // *** لاجیک مسدودی ***
    // اگر کاربر تایید نشده (0) باشد، فقط می‌تواند به ادمین (target_id=1) پیام دهد.
    if ($is_approved == 0) {
        if ($type != 'dm' || $tid != 1) {
            echo json_encode(['status'=>'error', 'msg'=>'حساب شما محدود است. فقط می‌توانید با پشتیبانی (ادمین) تماس بگیرید.']);
            exit;
        }
    }

    $file_path = null;
    if (!empty($_FILES['file']['name'])) {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $name = uniqid() . '_' . time() . '.' . $ext;
        $path = '../uploads/' . $name;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $path)) $file_path = 'uploads/' . $name;
    }
    
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, file_path, file_type, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$uid, $tid, $type, $msg, $file_path, ($is_img==1?'image':($is_img==2?'voice':'file'))]);
    
    echo json_encode(['status'=>'ok']);
}

// --- لیست چت‌ها ---
elseif ($act == 'get_chats_list') {
    // لیست چت‌ها را می‌گیرد. برای کاربر مسدود، همه چیز را نشان می‌دهیم ولی اجازه ارسال را در تابع بالا بستیم.
    // اما برای تجربه کاربری بهتر، می‌توانیم اینجا هم فیلتر کنیم. فعلاً همه را نشان می‌دهیم تا پیام‌های قبلی را ببیند.
    $sql = "
    SELECT list.*, m.message as last_msg, m.created_at as last_time
    FROM (
        SELECT g.id, g.type, g.name, g.avatar, g.chat_key, 
               (SELECT COUNT(*) FROM messages WHERE target_id=g.id AND type='group' AND is_read=0 AND sender_id!=$uid) as unread
        FROM groups g JOIN group_members gm ON g.id = gm.group_id WHERE gm.user_id = ?
        UNION ALL
        SELECT u.id, 'dm' as type, CONCAT(u.first_name,' ',u.last_name) as name, u.avatar, '' as chat_key,
               (SELECT COUNT(*) FROM messages WHERE target_id=u.id AND type='dm' AND is_read=0 AND sender_id!=$uid) as unread
        FROM users u JOIN user_contacts uc ON u.id = uc.contact_id WHERE uc.owner_id = ?
        UNION ALL
        -- اضافه کردن دستی چت ادمین (System) اگر در لیست نباشد
        SELECT 1 as id, 'dm' as type, 'پشتیبانی (ادمین)' as name, 'assets/img/chakavak.png' as avatar, '' as chat_key,
               (SELECT COUNT(*) FROM messages WHERE target_id=1 AND type='dm' AND is_read=0 AND sender_id!=$uid) as unread
    ) as list
    LEFT JOIN messages m ON m.id = (SELECT id FROM messages WHERE target_id=list.id AND type=list.type ORDER BY id DESC LIMIT 1)
    ORDER BY last_time DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$uid, $uid]);
    echo json_encode(['status'=>'ok', 'list'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

// --- دریافت پیام‌های یک چت ---
elseif ($act == 'get_messages') {
    $tid = $_POST['target_id'];
    $type = $_POST['type'];
    
    $key = "";
    $can_write = true;
    
    // اگر مسدود است و چت با ادمین نیست -> Read Only
    if ($is_approved == 0 && ($type != 'dm' || $tid != 1)) {
        $can_write = false;
    }

    if($type == 'group' || $type == 'channel') {
        $g = $pdo->query("SELECT chat_key FROM groups WHERE id=$tid")->fetch();
        $key = $g['chat_key'] ?? '';
    }

    $msgs = $pdo->prepare("SELECT m.*, u.first_name, u.avatar FROM messages m LEFT JOIN users u ON m.sender_id = u.id WHERE m.target_id=? AND m.type=? ORDER BY m.id ASC");
    $msgs->execute([$tid, $type]);
    
    // مارک خوانده شده
    $pdo->prepare("UPDATE messages SET is_read=1 WHERE target_id=? AND type=? AND sender_id!=?")->execute([$tid, $type, $uid]);

    echo json_encode([
        'status'=>'ok', 'list'=>$msgs->fetchAll(PDO::FETCH_ASSOC), 
        'chat_key'=>$key, 'can_write'=>$can_write, 
        'members_count'=>0, 'header'=>['status'=>($type=='dm'?'online':'')]
    ]);
}
?>