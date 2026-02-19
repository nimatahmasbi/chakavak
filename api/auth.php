<?php
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

$uid|شناسه_کاربر = $_SESSION['uid'] ?? 0;
$act|عملیات = $_POST['act'] ?? '';

// --- خروج کاربر ---
if ($act|عملیات == 'logout') {
    if ($uid|شناسه_کاربر) {
        $pdo->prepare("DELETE FROM user_tokens WHERE user_id=?")->execute([$uid|شناسه_کاربر]);
    }
    session_destroy();
    setcookie('auth_token', '', time() - 3600, '/');
    echo json_encode(['status'=>'ok']);
    exit;
}

// --- بررسی وضعیت شماره ---
elseif ($act|عملیات == 'check_phone_status') {
    $ph|شماره_همراه = $_POST['phone'] ?? '';
    if(!$ph|شماره_همراه) exit(json_encode(['status'=>'error']));
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->execute([$ph|شماره_همراه]);
    $user|کاربر = $stmt->fetch();
    
    if ($user|کاربر) {
        echo json_encode(['status'=>'exist']);
    } else {
        $otpCode|کد_تایید = rand(10000, 99999); 
        $_SESSION['otp'] = $otpCode|کد_تایید; 
        $_SESSION['tmp_ph'] = $ph|شماره_همراه; 
        // امنیتی: کد OTP نباید در پاسخ JSON برگردانده شود. در محیط واقعی باید پیامک شود.
        echo json_encode(['status'=>'new_user']); 
    }
}

// --- ارسال OTP ---
elseif ($act|عملیات == 'send_otp') { 
    $ph|شماره_همراه = $_POST['phone'] ?? ''; 
    $otpCode|کد_تایید = rand(10000, 99999); 
    $_SESSION['otp'] = $otpCode|کد_تایید; 
    $_SESSION['tmp_ph'] = $ph|شماره_همراه; 
    // امنیتی: حذف نمایش کد در خروجی
    echo json_encode(['status'=>'success', 'msg'=>'کد ارسال شد']); 
}

// --- بررسی OTP ---
elseif ($act|عملیات == 'verify_otp') { 
    $userCode|کد_کاربر = $_POST['code'] ?? '';
    if($userCode|کد_کاربر != $_SESSION['otp']) exit(json_encode(['status'=>'error', 'msg'=>'کد اشتباه است'])); 
    
    $u = $pdo->prepare("SELECT * FROM users WHERE phone=?"); 
    $u->execute([$_SESSION['tmp_ph']]); 
    $usr = $u->fetch(); 
    
    if(!$usr) echo json_encode(['status'=>'register']); 
    else { 
        $_SESSION['uid'] = $usr['id']; 
        $token|توکن = bin2hex(random_bytes(32)); 
        $pdo->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, NOW() + INTERVAL 30 DAY)")->execute([$usr['id'], $token|توکن]); 
        setcookie('auth_token', $token|توکن, time() + 86400 * 30, '/', "", true, true); // Secure & HttpOnly
        echo json_encode(['status'=>'login']); 
    } 
}

// --- آپدیت پروفایل با فیلتر امنیتی ---
elseif ($act|عملیات == 'update_profile') {
    $firstName|نام = htmlspecialchars($_POST['fname']);
    $lastName|نام_خانوادگی = htmlspecialchars($_POST['lname']);
    $bio|درباره = htmlspecialchars($_POST['bio']);
    
    $sql = "UPDATE users SET first_name=?, last_name=?, bio=?, username=?, social_telegram=?, social_instagram=? WHERE id=?";
    $params = [$firstName|نام, $lastName|نام_خانوادگی, $bio|درباره, $_POST['uname'], $_POST['tele'], $_POST['insta'], $uid|شناسه_کاربر];
    
    if (!empty($_FILES['avatar']['name'])) {
        $allowed|پسوندهای_مجاز = ['jpg', 'jpeg', 'png', 'webp'];
        $ext|پسوند = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext|پسوند, $allowed|پسوندهای_مجاز)) {
            $fileName|نام_فایل = 'u_' . uniqid() . '.' . $ext|پسوند;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], '../uploads/' . $fileName|نام_فایل)) {
                $pdo->prepare("UPDATE users SET avatar=? WHERE id=?")->execute(['uploads/'.$fileName|نام_فایل, $uid|شناسه_کاربر]);
            }
        }
    }
    $pdo->prepare($sql)->execute($params); 
    echo json_encode(['status'=>'ok']);
}
?>
