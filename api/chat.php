<?php
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

$act|عملیات = $_POST['act'] ?? $_POST['action'] ?? '';
$uid|شناسه_کاربر = $_SESSION['uid'] ?? 0;

if (!$uid|شناسه_کاربر) exit(json_encode(['status'=>'error', 'msg'=>'Auth error']));

// --- ارسال پیام (اصلاح شده) ---
if ($act|عملیات == 'send_message') {
    $targetId|شناسه_مقصد = $_POST['target_id']; 
    $chatType|نوع_چت = $_POST['type']; 
    // فیلتر متن پیام برای جلوگیری از XSS
    $rawMsg|متن_خام = $_POST['message'] ?? '';
    $cleanMsg|متن_ایمن = htmlspecialchars($rawMsg|متن_خام, ENT_QUOTES, 'UTF-8');
    $isImg = $_POST['is_image'] ?? 0;

    $filePath|مسیر_فایل = null;
    if (!empty($_FILES['file']['name'])) {
        $allowedExts|پسوندهای_مجاز = ['jpg', 'jpeg', 'png', 'gif', 'mp3', 'ogg', 'pdf', 'zip'];
        $fileExt|پسوند_فایل = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExt|پسوند_فایل, $allowedExts|پسوندهای_مجاز)) {
            exit(json_encode(['status'=>'error', 'msg'=>'فرمت فایل مجاز نیست']));
        }

        $dir = '../uploads/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $newFileName|نام_جدید = uniqid() . '_' . time() . '.' . $fileExt|پسوند_فایل;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $dir . $newFileName|نام_جدید)) {
            $filePath|مسیر_فایل = 'uploads/' . $newFileName|نام_جدید;
        }
    }
    
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, file_path, file_type, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$uid|شناسه_کاربر, $targetId|شناسه_مقصد, $chatType|نوع_چت, $cleanMsg|متن_ایمن, $filePath|مسیر_فایل, ($isImg==1?'image':($isImg==2?'voice':'file'))]);
    
    echo json_encode(['status'=>'ok']);
}
?>
