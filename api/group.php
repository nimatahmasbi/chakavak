<?php
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

$act = $_POST['act'] ?? '';
$uid = $_SESSION['uid'] ?? 0;
if (!$uid) exit(json_encode(['status'=>'error']));

// --- ساخت گروه ---
if ($act == 'create_group') {
    $approved = $pdo->query("SELECT is_approved FROM users WHERE id=$uid")->fetchColumn();
    if ($approved == 0) {
        echo json_encode(['status'=>'error', 'msg'=>'حساب شما محدود است.']);
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $type = $_POST['gtype'] ?? 'group';
    if (empty($name)) exit(json_encode(['status'=>'error', 'msg'=>'نام خالی است']));

    $stmt = $pdo->prepare("INSERT INTO groups (name, type, created_at, is_deleted) VALUES (?, ?, NOW(), 0)");
    $stmt->execute([$name, $type]);
    $gid = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role, joined_at) VALUES (?, ?, 'admin', NOW())");
    $stmt->execute([$gid, $uid]);
    
    $key = bin2hex(random_bytes(16));
    $pdo->prepare("UPDATE groups SET chat_key = ? WHERE id = ?")->execute([$key, $gid]);
    
    echo json_encode(['status'=>'ok', 'group_id'=>$gid]);
}

// --- اطلاعات گروه ---
elseif ($act == 'get_group_details') {
    $gid = $_POST['group_id'];
    $g = $pdo->prepare("SELECT * FROM groups WHERE id=? AND is_deleted=0");
    $g->execute([$gid]);
    $group = $g->fetch(PDO::FETCH_ASSOC);
    if (!$group) exit(json_encode(['status'=>'error', 'msg'=>'گروه یافت نشد یا حذف شده است']));
    
    $me = $pdo->prepare("SELECT role FROM group_members WHERE group_id=? AND user_id=?");
    $me->execute([$gid, $uid]);
    $myRole = $me->fetch();
    
    $m = $pdo->prepare("SELECT u.id, u.first_name, u.last_name, u.avatar, gm.role FROM users u JOIN group_members gm ON u.id=gm.user_id WHERE gm.group_id=?");
    $m->execute([$gid]);
    
    echo json_encode([
        'status'=>'ok', 
        'group'=>$group, 
        'members'=>$m->fetchAll(PDO::FETCH_ASSOC), 
        'is_admin'=> ($myRole && $myRole['role']=='admin')
    ]);
}

// --- حذف گروه (Soft Delete) ---
elseif ($act == 'soft_delete_group') {
    $gid = $_POST['group_id'];
    
    // بررسی اینکه آیا کاربر مدیر گروه است؟
    $check = $pdo->prepare("SELECT role FROM group_members WHERE group_id=? AND user_id=?");
    $check->execute([$gid, $uid]);
    $role = $check->fetchColumn();
    
    if ($role == 'admin') {
        // به جای حذف کامل، فقط پرچم حذف را ۱ می‌کنیم
        $pdo->prepare("UPDATE groups SET is_deleted=1 WHERE id=?")->execute([$gid]);
        echo json_encode(['status'=>'ok']);
    } else {
        echo json_encode(['status'=>'error', 'msg'=>'فقط مدیر گروه می‌تواند گروه را حذف کند']);
    }
}

// --- افزودن عضو ---
elseif ($act == 'add_group_member') {
    $gid = $_POST['group_id'];
    $target = $_POST['target'];
    
    if(is_numeric($target)) {
        $u = $pdo->prepare("SELECT id FROM users WHERE phone=? OR id=?");
        $u->execute([$target, $target]);
    } else {
        $u = $pdo->prepare("SELECT id FROM users WHERE username=?");
        $u->execute([$target]);
    }
    
    $user = $u->fetch();
    if ($user) {
        $pdo->prepare("INSERT IGNORE INTO group_members (group_id, user_id, role) VALUES (?, ?, 'member')")->execute([$gid, $user['id']]);
        echo json_encode(['status'=>'ok']);
    } else {
        echo json_encode(['status'=>'error', 'msg'=>'کاربر یافت نشد']);
    }
}

// --- حذف عضو / ترک گروه ---
elseif ($act == 'remove_group_member') {
    $gid = $_POST['group_id'];
    $tid = $_POST['user_id'];
    
    $check = $pdo->prepare("SELECT role FROM group_members WHERE group_id=? AND user_id=?");
    $check->execute([$gid, $uid]);
    $role = $check->fetchColumn();
    
    if ($role == 'admin' || $uid == $tid) {
        $pdo->prepare("DELETE FROM group_members WHERE group_id=? AND user_id=?")->execute([$gid, $tid]);
        echo json_encode(['status'=>'ok']);
    } else {
        echo json_encode(['status'=>'error', 'msg'=>'دسترسی ندارید']);
    }
}

// --- ویرایش گروه ---
elseif ($act == 'edit_group') {
    $gid = $_POST['group_id'];
    $name = $_POST['name'];
    
    $sql = "UPDATE groups SET name=? WHERE id=?";
    $params = [$name, $gid];
    
    if (!empty($_FILES['avatar']['name'])) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $fname = 'g_' . uniqid() . '.' . $ext;
        if(move_uploaded_file($_FILES['avatar']['tmp_name'], '../uploads/' . $fname)) {
            $sql = "UPDATE groups SET name=?, avatar=? WHERE id=?";
            $params = [$name, 'uploads/'.$fname, $gid];
        }
    }
    $pdo->prepare($sql)->execute($params);
    echo json_encode(['status'=>'ok']);
}
?>