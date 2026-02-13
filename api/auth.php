<?php
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

$uid = $_SESSION['uid'] ?? 0;
$act = $_POST['act'] ?? '';

// --- خروج کاربر ---
if ($act == 'logout') {
    if ($uid) {
        $pdo->prepare("DELETE FROM user_tokens WHERE user_id=?")->execute([$uid]);
    }
    session_destroy();
    setcookie('auth_token', '', time() - 3600, '/');
    echo json_encode(['status'=>'ok']);
    exit;
}

// --- بررسی وضعیت شماره ---
if ($act == 'check_phone_status') {
    $ph = $_POST['phone'];
    if(!$ph) exit(json_encode(['status'=>'error']));
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->execute([$ph]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo json_encode(['status'=>'exist']);
    } else {
        $c = rand(10000, 99999); 
        $_SESSION['otp'] = $c; 
        $_SESSION['tmp_ph'] = $ph; 
        echo json_encode(['status'=>'new_user', 'otp_debug'=>$c]); 
    }
}

// --- لاگین با رمز عبور ---
elseif ($act == 'login_password') {
    $ph = $_POST['phone'];
    $pass = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$ph]);
    $user = $stmt->fetch();
    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['uid'] = $user['id'];
        $t = bin2hex(random_bytes(32));
        $pdo->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, NOW() + INTERVAL 30 DAY)")->execute([$user['id'], $t]);
        setcookie('auth_token', $t, time() + 86400 * 30, '/');
        echo json_encode(['status'=>'ok']);
    } else {
        echo json_encode(['status'=>'error', 'msg'=>'رمز اشتباه است']);
    }
}

// --- ارسال کد تایید ---
elseif ($act == 'send_otp') { 
    $ph = $_POST['phone']; 
    $c = rand(10000, 99999); 
    $_SESSION['otp'] = $c; 
    $_SESSION['tmp_ph'] = $ph; 
    echo json_encode(['status'=>'success', 'msg'=>$c]); 
}

// --- تایید کد ---
elseif ($act == 'verify_otp') { 
    if($_POST['code'] != $_SESSION['otp']) exit(json_encode(['status'=>'error', 'msg'=>'کد اشتباه است'])); 
    $u = $pdo->prepare("SELECT * FROM users WHERE phone=?"); 
    $u->execute([$_SESSION['tmp_ph']]); 
    $usr = $u->fetch(); 
    if(!$usr) echo json_encode(['status'=>'register']); 
    else { 
        $_SESSION['uid'] = $usr['id']; 
        $t = bin2hex(random_bytes(32)); 
        $pdo->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, NOW() + INTERVAL 30 DAY)")->execute([$usr['id'], $t]); 
        setcookie('auth_token', $t, time() + 86400 * 30, '/'); 
        echo json_encode(['status'=>'login']); 
    } 
}

// --- دریافت اطلاعات کاربر ---
elseif ($act == 'get_user_info') {
    if (!$uid) exit(json_encode(['status'=>'error', 'msg'=>'Unauthorized']));
    $tid = $_POST['uid'] ?? $uid;
    if ($tid == 1) { 
        echo json_encode(['status'=>'ok', 'data'=>['first_name'=>'پشتیبانی', 'last_name'=>'چکاوک', 'username'=>'admin', 'avatar'=>'assets/img/chakavak.png']]); 
        exit; 
    }
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, username, bio, avatar, social_telegram, social_instagram FROM users WHERE id=?");
    $stmt->execute([$tid]); 
    echo json_encode(['status'=>'ok', 'data'=>$stmt->fetch(PDO::FETCH_ASSOC)]);
}

// --- دریافت مخاطبین ---
elseif ($act == 'get_contacts') {
    if (!$uid) exit(json_encode(['status'=>'error']));
    $stmt = $pdo->prepare("SELECT u.id, u.first_name, u.last_name, u.username, u.avatar FROM users u JOIN user_contacts c ON u.id = c.contact_id WHERE c.owner_id = ? ORDER BY u.first_name ASC");
    $stmt->execute([$uid]); 
    echo json_encode(['status'=>'ok', 'list'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

// --- جستجوی مخاطب (با محدودیت) ---
elseif ($act == 'search_contact') {
    if (!$uid) exit(json_encode(['status'=>'error']));
    
    // چک کردن تایید
    $approved = $pdo->query("SELECT is_approved FROM users WHERE id=$uid")->fetchColumn();
    if ($approved == 0) {
        echo json_encode(['status'=>'error', 'msg'=>'حساب شما محدود است. امکان افزودن مخاطب وجود ندارد.']);
        exit;
    }

    $q = $_POST['query'];
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, username, avatar FROM users WHERE (username=? OR phone=?) AND id!=?");
    $stmt->execute([$q, $q, $uid]); 
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if($u){ 
        $pdo->prepare("INSERT IGNORE INTO user_contacts (owner_id, contact_id) VALUES (?, ?)")->execute([$uid, $u['id']]);
        echo json_encode(['status'=>'ok', 'user'=>$u]); 
    } else {
        echo json_encode(['status'=>'error']);
    }
}

// --- آپدیت پروفایل ---
elseif ($act == 'update_profile') {
    if (!$uid) exit(json_encode(['status'=>'error']));
    
    $sql = "UPDATE users SET first_name=?, last_name=?, bio=?, username=?, social_telegram=?, social_instagram=? WHERE id=?";
    $params = [$_POST['fname'], $_POST['lname'], $_POST['bio'], $_POST['uname'], $_POST['tele'], $_POST['insta'], $uid];
    
    if (!empty($_FILES['avatar']['name'])) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION); 
        $name = 'u_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], '../uploads/' . $name)) {
            $pdo->prepare("UPDATE users SET avatar=? WHERE id=?")->execute(['uploads/'.$name, $uid]);
        }
    }
    $pdo->prepare($sql)->execute($params); 
    echo json_encode(['status'=>'ok']);
}

// --- تکمیل ثبت نام ---
elseif ($act == 'register_complete') { 
    $d = $_POST;
    $pdo->prepare("INSERT INTO users (phone, username, first_name, last_name, password, is_approved) VALUES (?, ?, ?, ?, ?, 0)")
        ->execute([$_SESSION['tmp_ph'], $d['uname'], $d['fname'], $d['lname'], password_hash($d['pass'], PASSWORD_BCRYPT)]); 
    
    $newUid = $pdo->lastInsertId();
    $_SESSION['uid'] = $newUid;

    // پیام خوش‌آمدگویی
    $msg = "به پیام‌رسان چکاوک خوش آمدید. حساب شما در حال بررسی است.";
    $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (1, ?, 'dm', ?, NOW())")->execute([$newUid, $msg]);

    echo json_encode(['status'=>'ok']); 
}
?>