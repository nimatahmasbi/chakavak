<?php
if ($act == 'create_group') {
    $name = $_POST['name']; $type = $_POST['gtype']; if(empty($name)){echo json_encode(['status'=>'error']);exit;}
    $key = bin2hex(random_bytes(16));
    $pdo->prepare("INSERT INTO groups (name, creator_id, chat_key, type) VALUES (?,?,?,?)")->execute([$name, $uid, $key, $type]);
    $gid = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?,?, 'admin')")->execute([$gid, $uid]);
    echo json_encode(['status'=>'ok']);
}
elseif ($act == 'get_group_details') {
    $gid = $_POST['group_id']; $grp = $pdo->query("SELECT * FROM groups WHERE id=$gid")->fetch();
    if(!$grp){echo json_encode(['status'=>'error']);exit;}
    if(!$grp['avatar'] || $grp['avatar']=='group_default') $grp['avatar']='assets/img/default.png';
    $mems = $pdo->query("SELECT u.id, u.first_name, u.last_name, u.username, u.avatar, gm.role FROM group_members gm JOIN users u ON gm.user_id = u.id WHERE gm.group_id=$gid")->fetchAll();
    foreach($mems as &$m) if(!$m['avatar'] || $m['avatar']=='default') $m['avatar']='assets/img/default.png';
    $role = $pdo->query("SELECT role FROM group_members WHERE group_id=$gid AND user_id=$uid")->fetchColumn();
    echo json_encode(['status'=>'ok', 'group'=>$grp, 'members'=>$mems, 'is_admin'=>($role=='admin' || $grp['creator_id']==$uid)]);
}
elseif ($act == 'edit_group') {
    $gid = $_POST['group_id']; $name = $_POST['name'];
    if (!$pdo->query("SELECT 1 FROM groups WHERE id=$gid AND creator_id=$uid UNION SELECT 1 FROM group_members WHERE group_id=$gid AND user_id=$uid AND role='admin'")->fetch()) { echo json_encode(['status'=>'error', 'msg'=>'No Permission']); exit; }
    $path = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $path = 'uploads/grp_'.$gid.'_'.time().'.'.pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        compressImage($_FILES['avatar']['tmp_name'], $path, 70);
    }
    if($path) $pdo->prepare("UPDATE groups SET name=?, avatar=? WHERE id=?")->execute([$name, $path, $gid]);
    else $pdo->prepare("UPDATE groups SET name=? WHERE id=?")->execute([$name, $gid]);
    echo json_encode(['status'=>'ok']);
}
elseif ($act == 'add_group_member') {
    $gid=$_POST['group_id']; $t=$_POST['target'];
    $u=$pdo->prepare("SELECT id FROM users WHERE username=? OR phone=?"); $u->execute([$t,$t]); $usr=$u->fetch();
    if(!$usr){echo json_encode(['status'=>'error']);exit;}
    $pdo->prepare("INSERT IGNORE INTO group_members (group_id, user_id, role) VALUES (?,?, 'member')")->execute([$gid, $usr['id']]);
    echo json_encode(['status'=>'ok']);
}
elseif ($act == 'remove_group_member') {
    $pdo->prepare("DELETE FROM group_members WHERE group_id=? AND user_id=?")->execute([$_POST['group_id'], $_POST['user_id']]);
    echo json_encode(['status'=>'ok']);
}
?>