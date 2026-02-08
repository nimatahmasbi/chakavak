<?php
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

$uid = $_SESSION['uid'] ?? 0;
if (!$uid) exit(json_encode(['status'=>'error']));

$act = $_POST['act'] ?? '';

// ذخیره سابسکرایبشن وب پوش
if ($act == 'save_push_sub') {
    $endpoint = $_POST['endpoint'];
    $keys = json_decode($_POST['keys'], true);
    $p256dh = $keys['p256dh'] ?? '';
    $auth = $keys['auth'] ?? '';

    // استفاده از ON DUPLICATE KEY برای آپدیت توکن‌های تکراری
    $stmt = $pdo->prepare("INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), p256dh = VALUES(p256dh), auth = VALUES(auth)");
    $stmt->execute([$uid, $endpoint, $p256dh, $auth]);

    echo json_encode(['status'=>'ok']);
}
?>