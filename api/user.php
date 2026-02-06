<?php
// دریافت مخاطبین
if ($act == 'get_contacts') {
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, username, avatar FROM users WHERE id != ? ORDER BY first_name ASC");
    $stmt->execute([$uid]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // اصلاح مسیر آواتار
    foreach($users as &$u) {
        if($u['avatar'] == 'default' || empty($u['avatar'])) $u['avatar'] = 'assets/img/default.png';
    }
    echo json_encode(['status'=>'ok', 'list'=>$users]);
}

// دریافت اطلاعات کاربر (برای پروفایل)
elseif ($act == 'get_user_info') {
    $targetId = $_POST['uid'] ?? $uid;
    $u = $pdo->prepare("SELECT id, first_name, last_name, username, bio, avatar, social_telegram, social_instagram, social_whatsapp, social_linkedin FROM users WHERE id=?");
    $u->execute([$targetId]);
    $data = $u->fetch(PDO::FETCH_ASSOC);
    
    if(!$data) { echo json_encode(['status'=>'error', 'msg'=>'User not found']); exit; }
    if($data['avatar'] == 'default' || empty($data['avatar'])) $data['avatar'] = 'assets/img/default.png';
    
    echo json_encode(['status'=>'ok', 'data'=>$data]);
}

// آپدیت پروفایل
elseif ($act == 'update_profile') {
    $chk = $pdo->prepare("SELECT id FROM users WHERE username=? AND id!=?");
    $chk->execute([$_POST['uname'], $uid]);
    if($chk->fetch()) { echo json_encode(['status'=>'error', 'msg'=>'Username taken']); exit; }
    
    $pdo->prepare("UPDATE users SET first_name=?, last_name=?, bio=?, username=?, social_telegram=?, social_instagram=?, social_whatsapp=?, social_linkedin=? WHERE id=?")
        ->execute([$_POST['fname'], $_POST['lname'], $_POST['bio'], $_POST['uname'], $_POST['tele'], $_POST['insta'], $_POST['whats'], $_POST['linked'], $uid]);
    echo json_encode(['status'=>'ok']);
}

// دریافت آیدی پشتیبان
elseif ($act == 'get_support_id') {
    $sid = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetchColumn();
    echo json_encode(['status'=>'ok', 'support_id' => $sid ?: 1]);
}

// خروج
elseif ($act == 'logout') {
    setcookie('auth_token', '', time()-3600, '/');
    if($uid) $pdo->prepare("DELETE FROM user_tokens WHERE user_id=?")->execute([$uid]);
    session_destroy();
    echo json_encode(['status'=>'ok']);
}

// --- بخش احراز هویت (Auth) ---

elseif($act == 'send_otp'){ 
    $ph = $_POST['phone']; 
    $c = rand(10000, 99999); 
    $_SESSION['otp'] = $c; 
    $_SESSION['tmp_ph'] = $ph; 
    $e = $pdo->prepare("SELECT id FROM users WHERE phone=?"); 
    $e->execute([$ph]); 
    // در نسخه واقعی اینجا باید SMS ارسال شود. فعلا کد را برمیگردانیم.
    echo json_encode(['status'=>'success', 'msg'=>$c, 'exist'=>(bool)$e->fetch()]); 
}

elseif($act == 'verify_otp'){ 
    if($_POST['code'] != $_SESSION['otp']) exit(json_encode(['status'=>'error', 'msg'=>'Code invalid'])); 
    
    $u = $pdo->prepare("SELECT * FROM users WHERE phone=?"); 
    $u->execute([$_SESSION['tmp_ph']]); 
    $usr = $u->fetch(); 
    
    if(!$usr) {
        echo json_encode(['status'=>'register']); 
    } else { 
        $_SESSION['uid'] = $usr['id']; 
        $t = bin2hex(random_bytes(32)); 
        $pdo->prepare("INSERT INTO user_tokens (user_id,token,expires_at) VALUES (?,?,NOW()+INTERVAL 30 DAY)")->execute([$usr['id'], $t]); 
        setcookie('auth_token', $t, time()+86400*30, '/'); 
        echo json_encode(['status'=>'login']); 
    } 
}

elseif($act == 'register_complete'){ 
    $d = $_POST; 
    $pdo->prepare("INSERT INTO users (phone,username,first_name,last_name,password) VALUES (?,?,?,?,?)")
        ->execute([$_SESSION['tmp_ph'], $d['uname'], $d['fname'], $d['lname'], password_hash($d['pass'], PASSWORD_BCRYPT)]); 
    $_SESSION['uid'] = $pdo->lastInsertId(); 
    echo json_encode(['status'=>'ok']); 
}
?>