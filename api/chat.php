<?php
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

$act = $_POST['act'] ?? $_POST['action'] ?? '';
$uid = $_SESSION['uid'] ?? 0;

if (!$uid) exit(json_encode(['status'=>'error', 'msg'=>'Auth error']));

// --- لیست چت‌ها ---
if ($act == 'get_chats_list') {
    // نکته: ما chat_key را هم سلکت می‌کنیم
    $sql = "SELECT c.id, c.type, c.name, c.avatar, c.chat_key, 
            (SELECT message FROM messages WHERE target_id=c.id AND type=c.type ORDER BY id DESC LIMIT 1) as last_msg,
            (SELECT created_at FROM messages WHERE target_id=c.id AND type=c.type ORDER BY id DESC LIMIT 1) as last_time,
            (SELECT COUNT(*) FROM messages WHERE target_id=c.id AND type=c.type AND is_read=0 AND sender_id!=?) as unread
            FROM (
                SELECT id, 'dm' as type, CONCAT(first_name,' ',last_name) as name, avatar, '' as chat_key FROM users WHERE id IN (SELECT contact_id FROM user_contacts WHERE owner_id=?)
                UNION
                SELECT id, type, name, avatar, chat_key FROM groups WHERE id IN (SELECT group_id FROM group_members WHERE user_id=?)
            ) as c";
            
    // نکته: کوئری بالا ساده شده است. در نسخه واقعی باید لاجیک دقیق‌تری برای DM ها داشته باشید (مثلا جدول chats).
    // اما برای رفع مشکل فعلی، فرض می‌کنیم chat_key در جدول groups هست و برای DM ها فعلا خالی یا ثابت است.
    
    // اصلاح کوئری برای سرعت و دقت بیشتر (نسخه بهینه):
    $sql = "
    SELECT 
        list.*,
        m.message as last_msg,
        m.created_at as last_time
    FROM (
        -- گروه‌ها
        SELECT g.id, g.type, g.name, g.avatar, g.chat_key, 
               (SELECT COUNT(*) FROM messages WHERE target_id=g.id AND type='group' AND is_read=0 AND sender_id!=$uid) as unread
        FROM groups g
        JOIN group_members gm ON g.id = gm.group_id
        WHERE gm.user_id = ?
        
        UNION ALL
        
        -- دایرکت‌ها (مخاطبین)
        SELECT u.id, 'dm' as type, CONCAT(u.first_name,' ',u.last_name) as name, u.avatar, '' as chat_key,
               (SELECT COUNT(*) FROM messages WHERE target_id=u.id AND type='dm' AND is_read=0 AND sender_id!=$uid) as unread
        FROM users u
        JOIN user_contacts uc ON u.id = uc.contact_id
        WHERE uc.owner_id = ?
    ) as list
    LEFT JOIN messages m ON m.id = (
        SELECT id FROM messages WHERE target_id=list.id AND type=list.type ORDER BY id DESC LIMIT 1
    )
    ORDER BY last_time DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$uid, $uid]);
    $list = $stmt->fetchAll();
    
    // اصلاح آواتار
    foreach($list as &$c) {
        if(!$c['avatar'] || $c['avatar']=='default') $c['avatar']='assets/img/chakavak.png';
    }
    
    echo json_encode(['status'=>'ok', 'list'=>$list]);
}

// --- دریافت پیام‌ها ---
elseif ($act == 'get_messages') {
    $tid = $_POST['target_id'];
    $type = $_POST['type'];
    
    // دریافت کلید چت
    $key = "";
    $can_write = true;
    $header = ['status'=>'offline']; // پیش‌فرض
    
    if($type == 'group' || $type == 'channel') {
        $g = $pdo->prepare("SELECT chat_key, type FROM groups WHERE id=?");
        $g->execute([$tid]);
        $res = $g->fetch();
        $key = $res['chat_key'] ?? '';
        if($res['type'] == 'channel') {
            // چک کردن ادمین بودن برای نوشتن در کانال
            $adm = $pdo->prepare("SELECT role FROM group_members WHERE group_id=? AND user_id=?");
            $adm->execute([$tid, $uid]);
            $role = $adm->fetch()['role'] ?? 'member';
            if($role != 'admin') $can_write = false;
        }
    } 
    // برای DM معمولا کلید ثابت یا ترکیبی از ID هاست (فعلا خالی می‌گذاریم یا هندل می‌کنیم)
    
    // خواندن پیام‌ها
    $sql = "SELECT m.*, u.first_name, u.avatar 
            FROM messages m 
            LEFT JOIN users u ON m.sender_id = u.id 
            WHERE m.target_id=? AND m.type=? 
            ORDER BY m.id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tid, $type]);
    $msgs = $stmt->fetchAll();
    
    // مارک کردن به عنوان خوانده شده
    $pdo->prepare("UPDATE messages SET is_read=1 WHERE target_id=? AND type=? AND sender_id!=?")->execute([$tid, $type, $uid]);
    
    // دریافت وضعیت آنلاین (برای DM)
    if($type == 'dm') {
        // اینجا می‌توانید لاجیک آنلاین بودن واقعی را بگذارید (فعلا فرض)
        $header['status'] = 'online'; 
    }
    
    // تعداد اعضا
    $members_count = 0;
    if($type != 'dm') {
        $mc = $pdo->prepare("SELECT COUNT(*) FROM group_members WHERE group_id=?");
        $mc->execute([$tid]);
        $members_count = $mc->fetchColumn();
    }

    echo json_encode([
        'status'=>'ok', 
        'list'=>$msgs, 
        'chat_key'=>$key, 
        'can_write'=>$can_write,
        'header'=>$header,
        'members_count'=>$members_count
    ]);
}

// --- ارسال پیام ---
elseif ($act == 'send_message') {
    $tid = $_POST['target_id'];
    $type = $_POST['type'];
    $msg = $_POST['message']; // متن رمزنگاری شده از کلاینت می‌آید
    $is_img = $_POST['is_image'] ?? 0;
    $file_path = null;
    
    // آپلود فایل
    if (!empty($_FILES['file']['name'])) {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $name = uniqid() . '_' . time() . '.' . $ext;
        $path = '../uploads/' . $name;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
            $file_path = 'uploads/' . $name;
        }
    }
    
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, file_path, file_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$uid, $tid, $type, $msg, $file_path, ($is_img==1?'image':($is_img==2?'voice':'file'))]);
    
    // آپدیت زمان آخرین فعالیت چت (اختیاری)
    
    echo json_encode(['status'=>'ok']);
}
?>