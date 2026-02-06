<?php
// اگر فایل مستقیم صدا زده شد کانفیگ را لود کن، اگر از api.php آمد نیازی نیست
if (!defined('MASTER_SECRET')) {
    require_once __DIR__ . '/../ch-admin/config.php';
}
header('Content-Type: application/json');

// بررسی احراز هویت (به جز مراحل ورود و ثبت نام)
$uid = 0;
if (isset($_SESSION['uid'])) {
    $uid = $_SESSION['uid'];
} elseif (isset($_COOKIE['auth_token'])) {
    $stmt = $pdo->prepare("SELECT user_id FROM user_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$_COOKIE['auth_token']]);
    if ($row = $stmt->fetch()) {
        $uid = $row['user_id'];
        $_SESSION['uid'] = $uid;
    }
}

$act = $_POST['act'] ?? '';

// اگر کاربر لاگین نیست و عملیات نیاز به لاگین دارد
if (!$uid && !in_array($act, ['send_otp', 'verify_otp', 'register_complete'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Unauthorized']);
    exit;
}

// --------------------------------------------------------------------------
// 1. مدیریت مخاطبین
// --------------------------------------------------------------------------

if ($act == 'get_contacts') {
    // فقط کسانی که در جدول user_contacts هستند
    $sql = "SELECT u.id, u.first_name, u.last_name, u.username, u.avatar 
            FROM users u 
            JOIN user_contacts c ON u.id = c.contact_id 
            WHERE c.owner_id = ? 
            ORDER BY u.first_name ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$uid]);
    $users = $stmt->fetchAll();
    
    foreach($users as &$u) {
        if(!$u['avatar'] || $u['avatar']=='default') $u['avatar']='assets/img/chakavak.png';
    }
    echo json_encode(['status'=>'ok', 'list'=>$users]);
}

elseif ($act == 'search_contact') {
    $q = $_POST['query'];
    // جستجوی کاربر بر اساس نام کاربری یا موبایل
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, username, avatar FROM users WHERE (username=? OR phone=?) AND id!=?");
    $stmt->execute([$q, $q, $uid]); 
    $u = $stmt->fetch();
    
    if($u){ 
        // افزودن خودکار به مخاطبین
        $pdo->prepare("INSERT IGNORE INTO user_contacts (owner_id, contact_id) VALUES (?, ?)")
            ->execute([$uid, $u['id']]);

        if(!$u['avatar'] || $u['avatar']=='default') $u['avatar']='assets/img/chakavak.png';
        echo json_encode(['status'=>'ok', 'user'=>$u]); 
    }
    else {
        echo json_encode(['status'=>'error', 'msg'=>'User not found']);
    }
}

// --------------------------------------------------------------------------
// 2. مدیریت پروفایل
// --------------------------------------------------------------------------

elseif ($act == 'get_user_info') {
    $tid = $_POST['uid'] ?? $uid;
    $u = $pdo->prepare("SELECT id, first_name, last_name, username, bio, avatar, social_telegram, social_instagram, social_whatsapp, social_linkedin FROM users WHERE id=?");
    $u->execute([$tid]); 
    $d = $u->fetch();
    
    if(!$d['avatar'] || $d['avatar']=='default') $d['avatar']='assets/img/chakavak.png';
    echo json_encode(['status'=>'ok', 'data'=>$d]);
}

elseif ($act == 'update_profile') {
    // بررسی تکراری نبودن نام کاربری
    $chk = $pdo->prepare("SELECT 1 FROM users WHERE username=? AND id!=? UNION SELECT 1 FROM admins WHERE username=?");
    $chk->execute([$_POST['uname'], $uid, $_POST['uname']]);
    if($chk->fetch()) { echo json_encode(['status'=>'error', 'msg'=>'Username taken']); exit; }
    
    $pdo->prepare("UPDATE users SET first_name=?, last_name=?, bio=?, username=?, social_telegram=?, social_instagram=?, social_whatsapp=?, social_linkedin=? WHERE id=?")
        ->execute([$_POST['fname'], $_POST['lname'], $_POST['bio'], $_POST['uname'], $_POST['tele'], $_POST['insta'], $_POST['whats'], $_POST['linked'], $uid]);
    
    echo json_encode(['status'=>'ok']);
}

// --------------------------------------------------------------------------
// 3. احراز هویت (Login / Register)
// --------------------------------------------------------------------------

elseif($act == 'send_otp'){ 
    $ph = $_POST['phone']; 
    $c = rand(10000, 99999); 
    $_SESSION['otp'] = $c; 
    $_SESSION['tmp_ph'] = $ph; 
    
    // اینجا باید به پنل پیامک متصل شود. فعلاً کد را برمی‌گردانیم.
    $e = $pdo->prepare("SELECT id FROM users WHERE phone=?"); 
    $e->execute([$ph]); 
    
    echo json_encode(['status'=>'success', 'msg'=>$c, 'exist'=>(bool)$e->fetch()]); 
}

elseif($act == 'verify_otp'){ 
    if($_POST['code'] != $_SESSION['otp']) exit(json_encode(['status'=>'error'])); 
    
    $u = $pdo->prepare("SELECT * FROM users WHERE phone=?"); 
    $u->execute([$_SESSION['tmp_ph']]); 
    $usr = $u->fetch(); 
    
    if(!$usr) {
        echo json_encode(['status'=>'register']); 
    } else { 
        $_SESSION['uid'] = $usr['id']; 
        $t = bin2hex(random_bytes(32)); 
        $pdo->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, NOW() + INTERVAL 30 DAY)")
            ->execute([$usr['id'], $t]); 
        setcookie('auth_token', $t, time() + 86400 * 30, '/'); 
        echo json_encode(['status'=>'login']); 
    } 
}

elseif($act == 'register_complete'){ 
    $d = $_POST;
    $chk = $pdo->prepare("SELECT 1 FROM users WHERE username=? UNION SELECT 1 FROM admins WHERE username=?");
    $chk->execute([$d['uname'], $d['uname']]); 
    if($chk->fetch()) { echo json_encode(['status'=>'error', 'msg'=>'Username taken']); exit; }
    
    $pdo->prepare("INSERT INTO users (phone, username, first_name, last_name, password) VALUES (?, ?, ?, ?, ?)")
        ->execute([$_SESSION['tmp_ph'], $d['uname'], $d['fname'], $d['lname'], password_hash($d['pass'], PASSWORD_BCRYPT)]); 
    
    $_SESSION['uid'] = $pdo->lastInsertId(); 
    echo json_encode(['status'=>'ok']); 
}

elseif ($act == 'logout') {
    setcookie('auth_token', '', time()-3600, '/'); 
    if($uid) $pdo->prepare("DELETE FROM user_tokens WHERE user_id=?")->execute([$uid]); 
    session_destroy(); 
    echo json_encode(['status'=>'ok']);
}

// --------------------------------------------------------------------------
// 4. *** بخش جدید: ذخیره اشتراک پوش نوتیفیکیشن (VAPID) ***
// --------------------------------------------------------------------------

elseif ($act == 'save_push_sub') {
    $endpoint = $_POST['endpoint'] ?? '';
    $p256dh = $_POST['p256dh'] ?? '';
    $auth = $_POST['auth'] ?? '';

    if ($endpoint && $p256dh && $auth) {
        // استفاده از INSERT ... ON DUPLICATE KEY UPDATE
        // اگر این مرورگر قبلاً ثبت شده بود، فقط کلیدهایش را آپدیت کن
        $sql = "INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), p256dh = VALUES(p256dh), auth = VALUES(auth)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$uid, $endpoint, $p256dh, $auth]);
        
        echo json_encode(['status' => 'ok', 'msg' => 'Subscription saved']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid data']);
    }
}

?>