<?php
/**
 * English|Persian: Secure Auth API | ای‌پی‌آی احراز هویت امن
 */
require_once __DIR__ . '/../ch-admin/config.php';
header('Content-Type: application/json');

$act|عملیات = $_POST['act'] ?? '';
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

// --- مرحله ۱: ارسال کد تایید ---
if ($act|عملیات == 'send_otp') {
    $phone|شماره = htmlspecialchars($_POST['phone'] ?? '');
    if (empty($phone|شماره)) { exit(json_encode(['status' => 'error', 'msg' => 'Phone required'])); }

    $otpCode|کد_تایید = rand(10000, 99999);
    $_SESSION['otp'] = $otpCode|کد_تایید;
    $_SESSION['tmp_phone'] = $phone|شماره;

    if ($settings['sms_active'] == '1') {
        // در این بخش باید کد اتصال به پنل پیامک خود را قرار دهید
        // Sample: MySmsProvider::send($phone, "کد تایید شما: $otpCode");
        echo json_encode(['status' => 'ok', 'msg' => 'کد تایید پیامک شد.']);
    } else {
        // نمایش کد برای تست سریع (در صورت غیرفعال بودن پنل)
        echo json_encode([
            'status' => 'ok',
            'msg' => 'حالت تست: کد تایید شما ' . $otpCode|کد_تایید . ' است.',
            'test_mode' => true,
            'code' => $otpCode|کد_تایید
        ]);
    }
    exit;
}

// --- مرحله ۲: تایید کد و ورود ---
if ($act|عملیات == 'verify_otp') {
    $inputCode|کد_ورودی = $_POST['code'] ?? '';
    
    if ($inputCode|کد_ورودی == $_SESSION['otp']) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->execute([$_SESSION['tmp_phone']]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['status'] == 0) { exit(json_encode(['status' => 'error', 'msg' => 'حساب شما مسدود است'])); }
            $_SESSION['uid'] = $user['id'];
            echo json_encode(['status' => 'success', 'target' => 'dashboard']);
        } else {
            // چک کردن اجازه ثبت‌نام
            if ($settings['registration_enabled'] == '1') {
                echo json_encode(['status' => 'success', 'target' => 'complete_profile']);
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'ثبت‌نام کاربر جدید غیرفعال است.']);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'کد تایید اشتباه است.']);
    }
    exit;
}
