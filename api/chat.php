<?php
// ماژول جامع گفتگو (دریافت لیست، تاریخچه و ارسال پیام)
// Comprehensive Chat Module (Fetch List, History, and Send)
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

$uid = $_SESSION['uid'] ?? 0; 
// پشتیبانی همزمان از دو نام متغیر اکشن
$act = $_POST['act'] ?? $_POST['action'] ?? ''; 

if (!$uid) { exit(json_encode(['status'=>'error', 'msg'=>'Unauthorized access'])); }

// --- ۱. دریافت لیست گفتگوها (نمایش در سایدبار) ---
// 1. Fetch Chats List (For Sidebar)
if ($act == 'get_chats_list') {
    $sql = "SELECT DISTINCT u.id, u.first_name, u.last_name, u.username, u.avatar, u.status 
            FROM users u 
            JOIN messages m ON (u.id = m.sender_id OR u.id = m.target_id)
            WHERE (m.sender_id = ? OR m.target_id = ?) AND u.id != ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$uid, $uid, $uid]);
    echo json_encode(['status'=>'ok', 'list'=>$stmt->fetchAll()]);
    exit;
}

// --- ۲. دریافت تاریخچه پیام‌ها در یک چت مشخص ---
// 2. Fetch Message History for a specific chat
elseif ($act == 'fetch_history' || $act == 'get_messages') {
    $target = $_POST['target_id'] ?? 0;
    $type = $_POST['type'] ?? 'pv';
    
    if ($type == 'pv') {
        $sql = "SELECT * FROM messages WHERE (sender_id=? AND target_id=?) OR (sender_id=? AND target_id=?) ORDER BY created_at ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$uid, $target, $target, $uid]);
    } else {
        $sql = "SELECT m.*, u.first_name, u.avatar FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.target_id=? AND m.type='group' ORDER BY m.created_at ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$target]);
    }
    
    $msgs = $stmt->fetchAll();
    
    // تیک خوانده شدن پیام‌ها
    if ($type == 'pv' && $target > 0) {
        $pdo->prepare("UPDATE messages SET is_read=1 WHERE sender_id=? AND target_id=?")->execute([$target, $uid]);
    }
    
    echo json_encode(['status'=>'ok', 'data'=>$msgs, 'my_id'=>$uid]);
    exit;
}

// --- ۳. عملیات ارسال پیام ---
// 3. Send Message Operation
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

// --- در صورتی که اکشن نامعتبر بود ---
// Invalid action fallback
else {
    echo json_encode(['status'=>'error', 'msg'=>'Action not handled in chat module']);
    exit;
}
?>
