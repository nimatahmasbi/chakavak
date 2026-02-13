<?php
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

$uid = $_SESSION['uid'] ?? 0;
if (!$uid) exit(json_encode(['status'=>'error', 'msg'=>'Unauthorized']));

$act = $_POST['act'] ?? '';

// 1. دریافت وضعیت امنیتی
if ($act == 'get_security_status') {
    // الف) وضعیت کاربر (2FA)
    try {
        $u = $pdo->prepare("SELECT two_factor_enabled FROM users WHERE id = ?");
        $u->execute([$uid]);
        $user = $u->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $user = ['two_factor_enabled' => 0];
    }

    // ب) لیست کلیدهای عبور (Passkeys)
    $passkeys = [];
    try {
        // چک کردن وجود جدول برای جلوگیری از کرش
        $check = $pdo->query("SHOW TABLES LIKE 'user_passkeys'");
        if($check->rowCount() > 0) {
            $stmt = $pdo->prepare("SELECT id, name, created_at FROM user_passkeys WHERE user_id = ? ORDER BY id DESC");
            $stmt->execute([$uid]);
            $passkeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        $passkeys = [];
    }
    
    // ج) تنظیمات سیستم
    try {
        $settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Exception $e) {
        $settings = [];
    }

    echo json_encode([
        'status' => 'ok',
        'user_2fa' => isset($user['two_factor_enabled']) ? (int)$user['two_factor_enabled'] : 0,
        'passkeys' => $passkeys,
        'system_2fa' => ($settings['enable_2fa'] ?? '0') == '1',
        'system_passkey' => ($settings['enable_passkey'] ?? '0') == '1'
    ]);
}

// 2. تغییر وضعیت تایید دو مرحله‌ای
elseif ($act == 'toggle_2fa') {
    try {
        $state = $_POST['enable'] == '1' ? 1 : 0;
        
        // اطمینان از وجود ستون
        try {
            $pdo->query("SELECT two_factor_enabled FROM users LIMIT 1");
        } catch (Exception $e) {
            $pdo->exec("ALTER TABLE users ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0");
        }

        $pdo->prepare("UPDATE users SET two_factor_enabled = ? WHERE id = ?")->execute([$state, $uid]);
        echo json_encode(['status' => 'ok', 'new_state' => $state]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'msg' => 'خطای دیتابیس']);
    }
}

// 3. شروع ثبت نام Passkey
elseif ($act == 'passkey_register_start') {
    $challenge = bin2hex(random_bytes(32));
    // ذخیره در سشن برای تایید بعدی (در پیاده‌سازی واقعی)
    // $_SESSION['webauthn_challenge'] = $challenge;
    
    $u = $pdo->prepare("SELECT username, first_name, last_name FROM users WHERE id=?");
    $u->execute([$uid]);
    $user = $u->fetch();

    echo json_encode([
        'status' => 'ok',
        'challenge' => $challenge,
        'user' => [
            'id' => $uid,
            'name' => $user['username'],
            'displayName' => $user['first_name'] . ' ' . $user['last_name']
        ]
    ]);
}

// 4. تکمیل ثبت نام Passkey
elseif ($act == 'passkey_register_finish') {
    try {
        // ساخت جدول اگر وجود ندارد
        $pdo->exec("CREATE TABLE IF NOT EXISTS `user_passkeys` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `credential_id` text NOT NULL,
            `public_key` text NOT NULL,
            `name` varchar(100) DEFAULT 'Device',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $stmt = $pdo->prepare("INSERT INTO user_passkeys (user_id, credential_id, public_key, name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$uid, $_POST['credential_id'], $_POST['public_key'], $_POST['device_name']]);
        echo json_encode(['status'=>'ok']);
    } catch (Exception $e) {
        echo json_encode(['status'=>'error', 'msg'=>$e->getMessage()]);
    }
}

// 5. حذف Passkey
elseif ($act == 'delete_passkey') {
    $kid = $_POST['key_id'];
    $pdo->prepare("DELETE FROM user_passkeys WHERE id = ? AND user_id = ?")->execute([$kid, $uid]);
    echo json_encode(['status' => 'ok']);
}
?>