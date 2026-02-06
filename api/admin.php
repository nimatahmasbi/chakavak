<?php
if (!isset($_SESSION['admin'])) { echo json_encode(['status'=>'error', 'msg'=>'Unauthorized']); exit; }

// 1. لیست‌ها و آمار
if ($act == 'admin_get_lists') {
    $t = $_POST['list_type']; 
    $data = [];
    
    if ($t == 'users') {
        $data = $pdo->query("SELECT * FROM users ORDER BY id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $data = $pdo->query("SELECT g.*, u.username as creator_user, u.first_name, u.last_name FROM groups g LEFT JOIN users u ON g.creator_id=u.id ORDER BY g.id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
    }
    if(!$data) $data=[];
    
    $s = [
        'users'=>$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(), 
        'groups'=>$pdo->query("SELECT COUNT(*) FROM groups")->fetchColumn(), 
        'msgs'=>$pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn()
    ];
    echo json_encode(['status'=>'ok', 'list'=>$data, 'stats'=>$s]);
}

// 2. تغییر وضعیت کاربر (مسدود/آزاد) - *** بخش جدید ***
elseif ($act == 'admin_toggle_user') {
    $uid = $_POST['user_id'];
    $pdo->prepare("UPDATE users SET is_approved = NOT is_approved WHERE id=?")->execute([$uid]);
    echo json_encode(['status'=>'ok']);
}

// 3. تنظیمات
elseif ($act == 'admin_get_settings') {
    echo json_encode(['status'=>'ok', 'data'=>$pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR)]);
}
elseif ($act == 'admin_save_settings') {
    $s = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");
    foreach(['ippanel_key','ippanel_line','enable_2fa','enable_passkey'] as $k) $s->execute([$k, $_POST[$k]??'']);
    echo json_encode(['status'=>'ok']);
}

// 4. مدیریت پیام‌های گروه
elseif ($act == 'admin_get_group_msgs') {
    $g=$_POST['group_id']; 
    $k=$pdo->query("SELECT chat_key FROM groups WHERE id=$g")->fetchColumn();
    $m=$pdo->query("SELECT m.*, u.first_name, u.last_name FROM messages m LEFT JOIN users u ON m.sender_id=u.id WHERE group_id=$g ORDER BY m.created_at ASC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status'=>'ok', 'list'=>$m?:[], 'chat_key'=>$k]);
}

// 5. مسدود/آزاد کردن گروه
elseif ($act == 'admin_ban_group') { 
    $pdo->prepare("UPDATE groups SET is_banned = NOT is_banned WHERE id=?")->execute([$_POST['group_id']]); 
    echo json_encode(['status'=>'ok']); 
}

// 6. دریافت و ویرایش کاربر
elseif ($act == 'admin_get_user') { 
    $u=$pdo->prepare("SELECT * FROM users WHERE id=?"); 
    $u->execute([$_POST['user_id']]); 
    echo json_encode(['status'=>'ok', 'data'=>$u->fetch(PDO::FETCH_ASSOC)]); 
}
elseif ($act == 'admin_edit_user') {
    $id=$_POST['user_id']; $u=$_POST['username'];
    $c=$pdo->prepare("SELECT 1 FROM users WHERE username=? AND id!=?"); 
    $c->execute([$u,$id]); 
    if($c->fetch()){echo json_encode(['status'=>'error','msg'=>'Duplicate']);exit;}
    
    $sql="UPDATE users SET first_name=?, last_name=?, username=?, phone=?, bio=?, social_telegram=?, social_instagram=?, social_whatsapp=?, social_linkedin=?";
    $p=[$_POST['fname'],$_POST['lname'],$u,$_POST['phone'],$_POST['bio'],$_POST['telegram'],$_POST['instagram'],$_POST['whatsapp'],$_POST['linkedin']];
    
    if(!empty($_POST['password'])){$sql.=", password=?"; $p[]=password_hash($_POST['password'], PASSWORD_BCRYPT);}
    $sql.=" WHERE id=?"; $p[]=$id; 
    $pdo->prepare($sql)->execute($p); 
    echo json_encode(['status'=>'ok']);
}
elseif ($act == 'admin_add_user') {
    $c=$pdo->prepare("SELECT 1 FROM users WHERE phone=? OR username=?"); 
    $c->execute([$_POST['phone'],$_POST['username']]); 
    if($c->fetch()){echo json_encode(['status'=>'error']);exit;}
    
    $pdo->prepare("INSERT INTO users (first_name, last_name, username, phone, password, bio, social_telegram, social_instagram, social_whatsapp, social_linkedin, is_approved) VALUES (?,?,?,?,?,?,?,?,?,?,1)")
        ->execute([$_POST['fname'],$_POST['lname'],$_POST['username'],$_POST['phone'],password_hash($_POST['password'],PASSWORD_BCRYPT),$_POST['bio'],$_POST['telegram'],$_POST['instagram'],$_POST['whatsapp'],$_POST['linkedin']]);
    echo json_encode(['status'=>'ok']);
}

// 7. چت ادمین
elseif ($act == 'admin_get_dm_history') {
    $tid=$_POST['target_id']; $aid=1; $ids=[$aid,$tid]; sort($ids); $k=hash('sha256',$ids[0].'-'.$ids[1].'-'.MASTER_SECRET);
    $m=$pdo->prepare("SELECT m.*, 'dm' as type FROM messages m WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY created_at ASC");
    $m->execute([$aid,$tid,$tid,$aid]); 
    echo json_encode(['status'=>'ok', 'list'=>$m->fetchAll(PDO::FETCH_ASSOC), 'chat_key'=>$k]);
}
elseif ($act == 'admin_send_dm') { 
    $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, is_read) VALUES (1,?,?,0)")->execute([$_POST['target_id'],$_POST['message']]); 
    echo json_encode(['status'=>'ok']); 
}

// 8. حذف‌ها
elseif ($act == 'admin_delete_group') { 
    $g=$_POST['group_id']; 
    $pdo->exec("DELETE FROM groups WHERE id=$g"); 
    $pdo->exec("DELETE FROM messages WHERE group_id=$g"); 
    $pdo->exec("DELETE FROM group_members WHERE group_id=$g"); 
    echo json_encode(['status'=>'ok']); 
}
elseif ($act == 'admin_delete_msg') { 
    $pdo->exec("DELETE FROM messages WHERE id=".$_POST['msg_id']); 
    echo json_encode(['status'=>'ok']); 
}
?>