<?php
/**
 * English|Persian: Secure Authentication API | ای‌پی‌آی احراز هویت امن
 */
require_once __DIR__ . '/../ch-admin/config.php';
header('Content-Type: application/json');

$act|عملیات = $_POST['act'] ?? '';
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

// --- ارسال کد تایید - Send OTP ---
if ($act|عملیات == 'send_otp') {
    $phone|شماره = htmlspecialchars($_POST['phone']);
    $otpCode|کد_تایید = rand(10000, 99999);
    $_SESSION['otp'] = $otpCode|کد_تایید;
    $_SESSION['tmp_phone'] = $phone|شماره;

    if ($settings['sms_active'] == '1') {
        // در اینجا کد اتصال به پنل پیامک شما قرار می‌گیرد
        // sendSmsTask($phone|شماره, $otpCode|کد_تایید);
        echo json_encode(['status' => 'ok', 'message' => 'Code sent via SMS | کد پیامک شد.']);
    } else {
        // نمایش کد در حالت تست - Show Code in Test Mode
        echo json_encode([
            'status' => 'ok', 
            'message' => 'Test Mode: Code is ' . $otpCode|کد_تایید,
            'debug_otp' => $otpCode|کد_تایید
        ]);
    }
    exit;
}

// --- تایید کد و ورود - Verify OTP & Login ---
if ($act|عملیات == 'verify_otp') {
    $userCode = $_POST['code'] ?? '';
    if ($userCode == $_SESSION['otp']) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$_SESSION['tmp_phone']]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['uid'] = $user['id'];
            echo json_encode(['status' => 'success', 'action' => 'login']);
        } else {
            if ($settings['registration_enabled'] == '1') {
                echo json_encode(['status' => 'success', 'action' => 'register_needed']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Registration is disabled | ثبت‌نام غیرفعال است.']);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Code | کد نامعتبر است.']);
    }
    exit;
}
