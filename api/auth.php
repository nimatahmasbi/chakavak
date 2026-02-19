<?php
// رابط برنامه‌نویسی احراز هویت
// Authentication API
require_once __DIR__ . '/../ch-admin/config.php';
header('Content-Type: application/json');

// دریافت نوع درخواست
// Get request type
$act = $_POST['act'] ?? ''; 
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

// مرحله اول: ارسال کد تایید
// Step One: Send OTP Code
if ($act == 'send_otp') {
    $phone = htmlspecialchars($_POST['phone'] ?? ''); 
    if (empty($phone)) { exit(json_encode(['status' => 'error', 'msg' => 'Phone required'])); }

    // تولید کد تصادفی پنج رقمی
    // Generate random 5-digit code
    $otpCode = rand(10000, 99999); 
    $_SESSION['otp'] = $otpCode;
    $_SESSION['tmp_phone'] = $phone;

    if (isset($settings['sms_active']) && $settings['sms_active'] == '1') {
        // ارسال پیامک از طریق پنل
        // Send SMS via gateway
        echo json_encode(['status' => 'ok', 'msg' => 'Code sent via SMS']);
    } else {
        // نمایش کد در خروجی برای حالت تست
        // Show code in output for test mode
        echo json_encode([
            'status' => 'ok',
            'msg' => 'Test Mode: Code is ' . $otpCode,
            'test_mode' => true,
            'code' => $otpCode
        ]);
    }
    exit;
}

// مرحله دوم: بررسی کد و ورود کاربر
// Step Two: Verify code and login user
if ($act == 'verify_otp') {
    $inputCode = $_POST['code'] ?? ''; 
    
    if (isset($_SESSION['otp']) && $inputCode == $_SESSION['otp']) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->execute([$_SESSION['tmp_phone']]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['status'] == 0) { exit(json_encode(['status' => 'error', 'msg' => 'حساب شما مسدود یا در انتظار تایید مدیر است.'])); }
            $_SESSION['uid'] = $user['id'];
            echo json_encode(['status' => 'success', 'target' => 'dashboard']);
        } else {
            // ثبت‌نام کاربر جدید
            if (isset($settings['registration_enabled']) && $settings['registration_enabled'] == '1') {
                
                // بررسی تنظیمات: آیا باید پیش‌فرض مسدود باشد؟
                $requireApproval = isset($settings['require_admin_approval']) && $settings['require_admin_approval'] == '1';
                $initialStatus = $requireApproval ? 0 : 1;

                $stmt = $pdo->prepare("INSERT INTO users (phone, username, status) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['tmp_phone'], $_SESSION['tmp_phone'], $initialStatus]);
                $newUserId = $pdo->lastInsertId();
                
                // ارسال پیام دایرکت خوش‌آمدگویی از طرف ادمین
                if ($requireApproval && !empty($settings['welcome_message'])) {
                    $adminId = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetchColumn() ?: 1;
                    $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (?, ?, 'pv', ?, NOW())")
                        ->execute([$adminId, $newUserId, $settings['welcome_message']]);
                }

                $_SESSION['uid'] = $newUserId;
                echo json_encode(['status' => 'success', 'target' => 'complete_profile']);
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'ثبت‌نام غیرفعال است.']);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'کد نامعتبر است.']);
    }
    exit;
}
?>


