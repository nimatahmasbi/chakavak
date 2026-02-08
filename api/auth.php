<?php
// لود کردن کانفیگ
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

// بررسی سشن
$uid = $_SESSION['uid'] ?? 0;
if (isset($_COOKIE['auth_token']) && !$uid) {
    $stmt = $pdo->prepare("SELECT user_id FROM user_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$_COOKIE['auth_token']]);
    if ($row = $stmt->fetch()) {
        $uid = $row['user_id'];
        $_SESSION['uid'] = $uid;
    }
}

$act = $_POST['act'] ?? '';

// اگر لاگین نیست و اکشن عمومی نیست
if (!$uid && !in_array($act, ['send_otp', 'verify_otp', 'register_complete'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Unauthorized']);
    exit;
}

// --------------------------------------------------------------------------
// ثبت نام (همراه با پیام خودکار)
// --------------------------------------------------------------------------
if($act == 'register_complete'){ 
    $d = $_POST;
    // بررسی تکراری
    $chk = $pdo->prepare("SELECT 1 FROM users WHERE username=? OR phone=? UNION SELECT 1 FROM admins WHERE username=?");
    $chk->execute([$d['uname'], $_SESSION['tmp_ph'], $d['uname']]); 
    if($chk->fetch()) { echo json_encode(['status'=>'error', 'msg'=>'نام کاربری یا شماره تکراری است']); exit; }
    
    // ثبت نام با وضعیت غیرفعال (Pending)
    $pdo->prepare("INSERT INTO users (phone, username, first_name, last_name, password, is_approved) VALUES (?, ?, ?, ?, ?, 0)")
        ->execute([$_SESSION['tmp_ph'], $d['uname'], $d['fname'], $d['lname'], password_hash($d['pass'], PASSWORD_BCRYPT)]); 
    
    $newUid = $pdo->lastInsertId();
    $_SESSION['uid'] = $newUid; 

    // *** ارسال پیام خودکار از طرف مدیر (ID 1) ***
    // (فرض بر این است که ادمین با ID 1 در جدول users نیست، پس از system message استفاده می‌کنیم یا ادمین را هم در users اضافه می‌کنیم.
    // اما چون جدول messages رابطه foreign key ندارد، می‌توانیم sender_id=1 (که ادمین است) بفرستیم)
    $msg = "سلام " . $d['fname'] . " عزیز،\nثبت‌نام شما با موفقیت انجام شد.\nحساب شما در حال بررسی است. لطفاً منتظر فعال‌سازی توسط مدیر باشید.";
    $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (1, ?, 'dm', ?, NOW())")
        ->execute([$newUid, $msg]);

    // همچنین کاربر را به لیست کانتکت‌های خودش اضافه می‌کنیم تا بتواند پیام ادمین را ببیند (اگر سیستم کانتکت بیس باشد)
    // بهتر است ادمین (System) را به کانتکت همه اضافه کنیم یا لاجیک لیست چت را طوری بنویسیم که پیام‌ها را نشان دهد.
    // (در api/chat.php قبلی این موضوع لحاظ شده بود که پیام‌های موجود را نشان دهد)

    echo json_encode(['status'=>'ok']); 
}

// --------------------------------------------------------------------------
// سایر متدها (بدون تغییر لاجیک، فقط ساختار استاندارد)
// --------------------------------------------------------------------------
elseif($act == 'send_otp'){ 
    $ph = $_POST['phone']; $c = rand(10000, 99999); $_SESSION['otp'] = $c; $_SESSION['tmp_ph'] = $ph; 
    $e = $pdo->prepare("SELECT id FROM users WHERE phone=?"); $e->execute([$ph]); 
    echo json_encode(['status'=>'success', 'msg'=>$c, 'exist'=>(bool)$e->fetch()]); 
}

elseif($act == 'verify_otp'){ 
    if($_POST['code'] != $_SESSION['otp']) exit(json_encode(['status'=>'error'])); 
    $u = $pdo->prepare("SELECT * FROM users WHERE phone=?"); $u->execute([$_SESSION['tmp_ph']]); $usr = $u->fetch(); 
    if(!$usr) {
        echo json_encode(['status'=>'register']); 
    } else { 
        $_SESSION['uid'] = $usr['id']; 
        $t = bin2hex(random_bytes(32)); 
        $pdo->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, NOW() + INTERVAL 30 DAY)")->execute([$usr['id'], $t]); 
        setcookie('auth_token', $t, time() + 86400 * 30, '/'); 
        echo json_encode(['status'=>'login']); 
    } 
}

elseif ($act == 'get_contacts') {
    $stmt = $pdo->prepare("SELECT u.id, u.first_name, u.last_name, u.username, u.avatar FROM users u JOIN user_contacts c ON u.id = c.contact_id WHERE c.owner_id = ? ORDER BY u.first_name ASC");
    $stmt->execute([$uid]);
    echo json_encode(['status'=>'ok', 'list'=>$stmt->fetchAll()]);
}

elseif ($act == 'search_contact') {
    $q = $_POST['query'];
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, username, avatar FROM users WHERE (username=? OR phone=?) AND id!=?");
    $stmt->execute([$q, $q, $uid]); 
    $u = $stmt->fetch();
    if($u){ 
        $pdo->prepare("INSERT IGNORE INTO user_contacts (owner_id, contact_id) VALUES (?, ?)")->execute([$uid, $u['id']]);
        echo json_encode(['status'=>'ok', 'user'=>$u]); 
    } else echo json_encode(['status'=>'error']);
}

elseif ($act == 'get_user_info') {
    $tid = $_POST['uid'] ?? $uid;
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, username, bio, avatar, social_telegram, social_instagram, social_whatsapp, social_linkedin FROM users WHERE id=?");
    $stmt->execute([$tid]); 
    echo json_encode(['status'=>'ok', 'data'=>$stmt->fetch()]);
}

elseif ($act == 'update_profile') {
    $pdo->prepare("UPDATE users SET first_name=?, last_name=?, bio=?, username=?, social_telegram=?, social_instagram=?, social_whatsapp=?, social_linkedin=? WHERE id=?")
        ->execute([$_POST['fname'], $_POST['lname'], $_POST['bio'], $_POST['uname'], $_POST['tele'], $_POST['insta'], $_POST['whats'], $_POST['linked'], $uid]);
    echo json_encode(['status'=>'ok']);
}

elseif ($act == 'logout') {
    setcookie('auth_token', '', time()-3600, '/'); 
    if($uid) $pdo->prepare("DELETE FROM user_tokens WHERE user_id=?")->execute([$uid]); 
    session_destroy(); 
    echo json_encode(['status'=>'ok']);
}
?>