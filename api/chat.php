<?php
// رابط برنامه‌نویسی گفتگو و پیام‌ها
// Chat and Messaging API
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

// بررسی شناسه کاربر
// Check user ID
$uid = $_SESSION['uid'] ?? 0; 
$act = $_POST['act'] ?? ''; 

if (!$uid) { exit(json_encode(['status'=>'error', 'msg'=>'Unauthorized access'])); }

// عملیات ارسال پیام
// Send message operation
if ($act == 'send_message') {
    $target = $_POST['target_id']; 
    $type = $_POST['type']; 
    
    // پاکسازی متن برای جلوگیری از حملات
    // Sanitize text to prevent attacks
    $msg = htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8'); 
    
    $filePath = null; 
    $fileType = 'text';

    // پردازش فایل پیوست شده
    // Process attached file
    if (!empty($_FILES['file']['name'])) {
        $validExts = ['jpg','png','mp3','pdf','zip','voice'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $validExts)) {
            $newName = uniqid() . "_$uid." . $ext;
            if (!is_dir('../uploads')) mkdir('../uploads', 0777, true);
            
            // انتقال فایل به پوشه آپلود
            // Move file to uploads folder
            if (move_uploaded_file($_FILES['file']['tmp_name'], "../uploads/$newName")) {
                $filePath = "uploads/$newName";
                $fileType = in_array($ext, ['jpg','png']) ? 'image' : 'file';
            }
        }
    }

    // ذخیره پیام در پایگاه داده
    // Save message to database
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, file_path, file_type, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$uid, $target, $type, $msg, $filePath, $fileType]);
    echo json_encode(['status'=>'ok']);
}

// عملیات دریافت تاریخچه پیام‌ها
// Fetch message history operation
elseif ($act == 'fetch_history') {
    $target = $_POST['target_id'];
    $type = $_POST['type'];
    
    if ($type == 'pv') {
        // دریافت پیام‌های گفتگوی دونفره
        // Fetch private chat messages
        $sql = "SELECT * FROM messages WHERE (sender_id=? AND target_id=?) OR (sender_id=? AND target_id=?) ORDER BY created_at ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$uid, $target, $target, $uid]);
    } else {
        // دریافت پیام‌های گروه
        // Fetch group messages
        $sql = "SELECT m.*, u.first_name, u.avatar FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.target_id=? AND m.type='group' ORDER BY m.created_at ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$target]);
    }
    
    $msgs = $stmt->fetchAll();
    
    // تغییر وضعیت پیام‌ها به خوانده شده
    // Mark messages as read
    if ($type == 'pv') {
        $pdo->prepare("UPDATE messages SET is_read=1 WHERE sender_id=? AND target_id=?")->execute([$target, $uid]);
    }
    
    echo json_encode(['status'=>'ok', 'data'=>$msgs, 'my_id'=>$uid]);
}
?>