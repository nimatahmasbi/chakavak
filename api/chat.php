<?php
// ماژول جامع چت و گفتگو (حل مشکل JSON)
// Comprehensive Chat Module (JSON Fix)
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

$uid = $_SESSION['uid'] ?? 0; 
$act = $_POST['act'] ?? $_POST['action'] ?? ''; 

if (!$uid) { exit(json_encode(['status'=>'error', 'msg'=>'Unauthorized'])); }

// --- ۱. دریافت لیست گفتگوها برای سایدبار ---
// 1. Fetch Chats List for Sidebar
if ($act == 'get_chats_list') {
    // دریافت دایرکت‌ها (pv)
    $sqlPV = "SELECT u.id, u.first_name as name, u.avatar, 'dm' as type,
              (SELECT message FROM messages WHERE (sender_id=$uid AND target_id=u.id) OR (sender_id=u.id AND target_id=$uid) ORDER BY id DESC LIMIT 1) as last_msg,
              (SELECT COUNT(id) FROM messages WHERE sender_id=u.id AND target_id=$uid AND is_read=0) as unread
              FROM users u 
              JOIN messages m ON (u.id = m.sender_id OR u.id = m.target_id)
              WHERE (m.sender_id = $uid OR m.target_id = $uid) AND u.id != $uid
              GROUP BY u.id";
              
    // دریافت گروه‌ها
    $sqlGroup = "SELECT g.id, g.name, g.avatar, 'group' as type,
                 (SELECT message FROM messages WHERE target_id=g.id AND type='group' ORDER BY id DESC LIMIT 1) as last_msg,
                 0 as unread
                 FROM groups g
                 JOIN group_members gm ON g.id = gm.group_id
                 WHERE gm.user_id = $uid";
                 
    try {
        $pvList = $pdo->query($sqlPV)->fetchAll(PDO::FETCH_ASSOC);
        $groupList = $pdo->query($sqlGroup)->fetchAll(PDO::FETCH_ASSOC);
        
        $list = array_merge($pvList, $groupList);
        echo json_encode(['status'=>'ok', 'list'=>$list]);
    } catch (Exception $e) {
        echo json_encode(['status'=>'error', 'msg'=>'Database error']);
    }
    exit;
}

// --- ۲. دریافت پیام‌های یک گفتگوی خاص ---
// 2. Fetch specific chat messages
elseif ($act == 'fetch_history' || $act == 'get_messages') {
    $target = $_POST['target_id'] ?? 0;
    $type = $_POST['type'] ?? 'pv';
    
    if ($type == 'pv' || $type == 'dm') {
        $sql = "SELECT * FROM messages WHERE (sender_id=? AND target_id=?) OR (sender_id=? AND target_id=?) ORDER BY created_at ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$uid, $target, $target, $uid]);
    } else {
        $sql = "SELECT m.*, u.first_name, u.avatar FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.target_id=? AND m.type='group' ORDER BY m.created_at ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$target]);
    }
    
    $msgs = $stmt->fetchAll();
    
    // تیک دوم پیام‌ها (خوانده شده)
    if (($type == 'pv' || $type == 'dm') && $target > 0) {
        $pdo->prepare("UPDATE messages SET is_read=1 WHERE sender_id=? AND target_id=?")->execute([$target, $uid]);
    }
    
    echo json_encode(['status'=>'ok', 'data'=>$msgs, 'my_id'=>$uid]);
    exit;
}

// --- ۳. ارسال پیام ---
// 3. Send a new message
elseif ($act == 'send_message') {
    $target = $_POST['target_id'] ?? 0; 
    $type = $_POST['type'] ?? 'pv'; 
    $msg = htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8'); 
    
    $filePath = null; 
    $fileType = 'text';

    if (!empty($_FILES['file']['name'])) {
        $validExts = ['jpg','png','mp3','pdf','zip','voice'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $validExts)) {
            $newName = uniqid() . "_$uid." . $ext;
            if (!is_dir('../uploads')) mkdir('../uploads', 0777, true);
            if (move_uploaded_file($_FILES['file']['tmp_name'], "../uploads/$newName")) {
                $filePath = "uploads/$newName";
                $fileType = in_array($ext, ['jpg','png']) ? 'image' : 'file';
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, file_path, file_type, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$uid, $target, $type, $msg, $filePath, $fileType]);
    echo json_encode(['status'=>'ok']);
    exit;
}

// اگر هیچ اکشنی مچ نشد
else {
    echo json_encode(['status'=>'error', 'msg'=>'Action not found in chat api']);
    exit;
}
?>
