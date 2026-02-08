<?php
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

$uid = $_SESSION['uid'] ?? 0;
if (!$uid) exit(json_encode(['status'=>'error', 'msg'=>'Unauthorized']));

$act = $_POST['act'] ?? '';

if ($act == 'get_security_status') {
    // گرفتن وضعیت 2FA کاربر
    $u = $pdo->prepare("SELECT two_factor_enabled FROM users WHERE id = ?");
    $u->execute([$uid]);
    $user = $u->fetch();

    // گرفتن لیست Passkey ها
    // اگر جدول وجود نداشت، آرایه خالی برگردان (جلوگیری از خطا)
    try {
        $keys = $pdo->prepare("SELECT id, name, created_at FROM user_passkeys WHERE user_id = ?");
        $keys->execute([$uid]);
        $passkeys = $keys->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $passkeys = [];
    }
    
    // تنظیمات سیستم
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
elseif ($act == 'toggle_2fa') {
    $state = $_POST['enable'] == '1' ? 1 : 0;
    $pdo->prepare("UPDATE users SET two_factor_enabled = ? WHERE id = ?")->execute([$state, $uid]);
    echo json_encode(['status' => 'ok', 'new_state' => $state]);
}
// بقیه توابع Passkey...
elseif ($act == 'delete_passkey') {
    $kid = $_POST['key_id'];
    $pdo->prepare("DELETE FROM user_passkeys WHERE id = ? AND user_id = ?")->execute([$kid, $uid]);
    echo json_encode(['status' => 'ok']);
}
?>