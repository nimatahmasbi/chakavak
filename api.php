<?php
ini_set('display_errors', 0); error_reporting(E_ALL); header('Content-Type: application/json');

if (!file_exists(__DIR__ . '/ch-admin/config.php')) { echo json_encode(['status'=>'error', 'msg'=>'Config missing']); exit; }
require __DIR__ . '/ch-admin/config.php';

$act = $_POST['act'] ?? $_POST['action'] ?? '';
$uid = $_SESSION['uid'] ?? 0;
$isAdmin = isset($_SESSION['admin']);

if ($uid == 0 && !$isAdmin && !in_array($act, ['send_otp', 'verify_otp', 'register_complete'])) {
    echo json_encode(['status'=>'auth_error', 'msg'=>'Login required']); exit;
}

try {
    switch ($act) {
        // ماژول‌ها
        case 'get_chats_list': case 'get_messages': case 'send_message': require __DIR__.'/api/chat.php'; break;
        case 'create_group': case 'get_group_details': case 'edit_group': case 'add_group_member': case 'remove_group_member': require __DIR__.'/api/group.php'; break;
        case 'get_contacts': case 'search_contact': case 'get_user_info': case 'update_profile': case 'logout': case 'send_otp': case 'verify_otp': case 'register_complete': require __DIR__.'/api/auth.php'; break;
        
        // ماژول نوتیفیکیشن (جدید)
        case 'save_push_sub': require __DIR__.'/api/notify.php'; break;

        // امنیت
        case 'get_security_status': case 'toggle_2fa': case 'passkey_register_start': case 'passkey_register_finish': case 'delete_passkey': require __DIR__.'/api/security.php'; break;

        // ادمین
        case 'admin_get_lists': case 'admin_toggle_user': case 'admin_get_settings': case 'admin_save_settings': case 'admin_get_group_msgs': case 'admin_ban_group': case 'admin_get_user': case 'admin_edit_user': case 'admin_add_user': case 'admin_get_dm_history': case 'admin_send_dm': case 'admin_delete_group': case 'admin_delete_msg': require __DIR__.'/api/admin.php'; break;
        
        default: echo json_encode(['status'=>'error', 'msg'=>'Invalid Action']); break;
    }
} catch (Exception $e) { echo json_encode(['status'=>'error', 'msg'=>$e->getMessage()]); }
?>