<?php
// احراز هویت یکپارچه سیستم
// Unified System Authentication API
require_once __DIR__ . '/../ch-admin/config.php';
header('Content-Type: application/json');

$act = $_POST['act'] ?? ''; 
$settings = [];
try {
    $settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {}

// --- ۱. بررسی وضعیت شماره (ورود یا ثبت‌نام) ---
// 1. Check phone status (Login or Register)
if ($act == 'check_phone_status') {
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    
    if ($stmt->fetch()) {
        // کاربر وجود دارد -> هدایت به صفحه رمز
        echo json_encode(['status' => 'exist']);
    } else {
        // کاربر جدید -> ارسال خودکار کد تایید و هدایت به صفحه OTP
        $otpCode = rand(10000, 99999);
        $_SESSION['otp'] = $otpCode;
        $_SESSION['tmp_phone'] = $phone;
        
        $resp = ['status' => 'new_user'];
        if (!isset($settings['sms_active']) || $settings['sms_active'] == '0') {
            $resp['otp_debug'] = $otpCode; // نمایش کد در حالت تست
        }
        echo json_encode($resp);
    }
    exit;
}

// --- ۲. ورود با رمز عبور ---
// 2. Login with Password
if ($act == 'login_password') {
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $pass = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($pass, $user['password'])) {
        if ($user['status'] == 0) {
            exit(json_encode(['status' => 'error', 'msg' => 'حساب شما مسدود یا در انتظار تایید مدیر است.']));
        }
        $_SESSION['uid'] = $user['id'];
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'رمز عبور اشتباه است']);
    }
    exit;
}

// --- ۳. ارسال کد تایید (فراموشی رمز) ---
// 3. Send OTP Code
if ($act == 'send_otp') {
    $phone = htmlspecialchars($_POST['phone'] ?? ''); 
    $otpCode = rand(10000, 99999); 
    $_SESSION['otp'] = $otpCode;
    $_SESSION['tmp_phone'] = $phone;

    $resp = ['status' => 'success'];
    if (!isset($settings['sms_active']) || $settings['sms_active'] == '0') {
        $resp['msg'] = $otpCode; // حالت تست
    } else {
        $resp['msg'] = 'کد پیامک شد';
    }
    echo json_encode($resp);
    exit;
}

// --- ۴. تایید کد OTP ---
// 4. Verify OTP Code
if ($act == 'verify_otp') {
    $inputCode = $_POST['code'] ?? ''; 
    if (isset($_SESSION['otp']) && $inputCode == $_SESSION['otp']) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->execute([$_SESSION['tmp_phone']]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['status'] == 0) { exit(json_encode(['status' => 'error', 'msg' => 'حساب مسدود است.'])); }
            $_SESSION['uid'] = $user['id'];
            echo json_encode(['status' => 'login']); // همخوانی با login.js
        } else {
            if (isset($settings['registration_enabled']) && $settings['registration_enabled'] == '1') {
                echo json_encode(['status' => 'register']); // همخوانی با login.js
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'ثبت‌نام غیرفعال است.']);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'کد نامعتبر است.']);
    }
    exit;
}

// --- ۵. تکمیل ثبت‌نام کاربر جدید ---
// 5. Complete Registration
if ($act == 'register_complete') {
    $uname = htmlspecialchars($_POST['uname'] ?? '');
    $fname = htmlspecialchars($_POST['fname'] ?? '');
    $lname = htmlspecialchars($_POST['lname'] ?? '');
    $pass = password_hash($_POST['pass'] ?? '', PASSWORD_DEFAULT);
    $phone = $_SESSION['tmp_phone'] ?? '';
    
    if (!$phone) { exit(json_encode(['status' => 'error', 'msg' => 'خطا در نشست. مجدد تلاش کنید.'])); }
    
    $requireApproval = isset($settings['require_admin_approval']) && $settings['require_admin_approval'] == '1';
    $initialStatus = $requireApproval ? 0 : 1;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (phone, username, first_name, last_name, password, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$phone, $uname, $fname, $lname, $pass, $initialStatus]);
        $newUserId = $pdo->lastInsertId();
        
        // ارسال پیام خوش‌آمدگویی در صورت نیاز به تایید
        if ($requireApproval && !empty($settings['welcome_message'])) {
            $adminId = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetchColumn() ?: 1;
            $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (?, ?, 'pv', ?, NOW())")
                ->execute([$adminId, $newUserId, $settings['welcome_message']]);
        }
        
        if ($initialStatus == 1) {
            $_SESSION['uid'] = $newUserId; // لاگین خودکار اگر آزاد باشد
        }
        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'msg' => 'نام کاربری یا شماره از قبل وجود دارد.']);
    }
    exit;
}

// --- ۶. خروج از حساب کاربری ---
// 6. User Logout
if ($act == 'logout') {
    unset($_SESSION['uid']);
    // در صورت نیاز به پاک کردن کل سشن: session_destroy();
    echo json_encode(['status' => 'ok']);
    exit;
}
?>
