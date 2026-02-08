<?php
if (!defined('MASTER_SECRET')) { require_once __DIR__ . '/../ch-admin/config.php'; }
header('Content-Type: application/json');

$act = $_POST['act'] ?? $_POST['action'] ?? '';
$uid = $_SESSION['uid'] ?? 0;

if (!$uid) exit(json_encode(['status'=>'error', 'msg'=>'Auth error']));

// --- ساخت گروه/کانال ---
if ($act == 'create_group') {
    $name = $_POST['name'];
    $type = $_POST['gtype'] ?? 'group'; // group or channel
    
    // 1. ساخت گروه (بدون creator_id چون در جدول نیست)
    $stmt = $pdo->prepare("INSERT INTO groups (name, type, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$name, $type]);
    $gid = $pdo->lastInsertId();
    
    // 2. افزودن سازنده به عنوان ادمین
    $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role, joined_at) VALUES (?, ?, 'admin', NOW())");
    $stmt->execute([$gid, $uid]);
    
    // 3. تولید و ذخیره کلید رمزنگاری چت
    $key = bin2hex(random_bytes(16));
    $pdo->prepare("UPDATE groups SET chat_key = ? WHERE id = ?")->execute([$key, $gid]);
    
    echo json_encode(['status'=>'ok', 'group_id'=>$gid]);
}

// --- دریافت اطلاعات گروه ---
elseif ($act == 'get_group_details') {
    $gid = $_POST['group_id'];
    
    // اطلاعات گروه
    $g = $pdo->prepare("SELECT * FROM groups WHERE id=?");
    $g->execute([$gid]);
    $group = $g->fetch(PDO::FETCH_ASSOC);
    
    if (!$group) { echo json_encode(['status'=>'error', 'msg'=>'Group not found']); exit; }
    
    // چک کردن دسترسی کاربر جاری
    $me = $pdo->prepare("SELECT role FROM group_members WHERE group_id=? AND user_id=?");
    $me->execute([$gid, $uid]);
    $myRole = $me->fetch();
    
    // لیست اعضا
    $m = $pdo->prepare("SELECT u.id, u.first_name, u.last_name, u.avatar, gm.role FROM users u JOIN group_members gm ON u.id=gm.user_id WHERE gm.group_id=?");
    $m->execute([$gid]);
    $members = $m->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status'=>'ok', 
        'group'=>$group, 
        'members'=>$members, 
        'is_admin'=> ($myRole && $myRole['role']=='admin')
    ]);
}

// --- افزودن عضو ---
elseif ($act == 'add_group_member') {
    $gid = $_POST['group_id'];
    $target = $_POST['target']; // username or phone
    
    // پیدا کردن کاربر
    $u = $pdo->prepare("SELECT id FROM users WHERE username=? OR phone=?");
    $u->execute([$target, $target]);
    $user = $u->fetch();
    
    if ($user) {
        // چک کن قبلا عضو نباشد
        $exist = $pdo->prepare("SELECT 1 FROM group_members WHERE group_id=? AND user_id=?");
        $exist->execute([$gid, $user['id']]);
        if (!$exist->fetch()) {
            $pdo->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'member')")->execute([$gid, $user['id']]);
            echo json_encode(['status'=>'ok']);
        } else {
            echo json_encode(['status'=>'error', 'msg'=>'کاربر عضو است']);
        }
    } else {
        echo json_encode(['status'=>'error', 'msg'=>'کاربر یافت نشد']);
    }
}

// --- حذف عضو ---
elseif ($act == 'remove_group_member') {
    $gid = $_POST['group_id'];
    $tid = $_POST['user_id'];
    
    // فقط ادمین یا خود کاربر می‌تواند حذف کند (لاجیک ساده)
    $pdo->prepare("DELETE FROM group_members WHERE group_id=? AND user_id=?")->execute([$gid, $tid]);
    echo json_encode(['status'=>'ok']);
}

// --- ویرایش گروه ---
elseif ($act == 'edit_group') {
    $gid = $_POST['group_id'];
    $name = $_POST['name'];
    
    $sql = "UPDATE groups SET name=? WHERE id=?";
    $params = [$name, $gid];
    
    // آپلود آواتار گروه
    if (!empty($_FILES['avatar']['name'])) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $fname = 'g_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['avatar']['tmp_name'], '../uploads/' . $fname);
        
        $sql = "UPDATE groups SET name=?, avatar=? WHERE id=?";
        $params = [$name, 'uploads/'.$fname, $gid];
    }
    
    $pdo->prepare($sql)->execute($params);
    echo json_encode(['status'=>'ok']);
}
?>