<?php
// ماژول مدیریت کاربران و ارتباط مستقیم
// User Management & Direct Communication Module
if (!defined('MASTER_SECRET')) { exit; }

$adminId = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetchColumn() ?: 1;

if (isset($_POST['act'])) {
    // تغییر وضعیت کاربر و ارسال پیام تایید
    if ($_POST['act'] == 'toggle_status') {
        $uid = (int)$_POST['user_id'];
        $newStatus = (int)$_POST['new_status'];
        $pdo->prepare("UPDATE users SET status=? WHERE id=?")->execute([$newStatus, $uid]);
        
        // ارسال پیام تایید در صورت فعال شدن حساب
        if ($newStatus == 1) { 
            $actMsg = $pdo->query("SELECT s_value FROM settings WHERE s_key='activation_message'")->fetchColumn();
            if(!empty($actMsg)) {
                $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (?, ?, 'pv', ?, NOW())")->execute([$adminId, $uid, $actMsg]);
            }
        }
        echo "<div style='background:#d4edda; color:#155724; padding:15px; margin-bottom:20px; border-radius:5px;'>✅ وضعیت کاربر تغییر یافت.</div>";
    }

    // ارسال پیام دایرکت از طریق Modal
    elseif ($_POST['act'] == 'send_pv_modal') {
        $targetId = (int)$_POST['user_id'];
        $msg = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');
        $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (?, ?, 'pv', ?, NOW())")->execute([$adminId, $targetId, $msg]);
        echo "<div style='background:#d1ecf1; color:#0c5460; padding:15px; margin-bottom:20px; border-radius:5px;'>✉️ پیام با موفقیت دایرکت شد.</div>";
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<div style="padding: 20px; font-family: Tahoma, 'Vazirmatn', sans-serif; direction: rtl; text-align: right; background: #f9f9f9;">
    <h3 style="color: #555;">📋 لیست و مدیریت کاربران</h3>
    
    <div style="overflow-x: auto; background: white; border-radius: 8px; border: 1px solid #e0e0e0; padding: 15px;">
        <table style="width: 100%; border-collapse: collapse; text-align: right;">
            <thead style="background: #f4f6f8; border-bottom: 2px solid #ddd;">
                <tr>
                    <th style="padding: 12px; border-left: 1px solid #eee;">ID</th>
                    <th style="padding: 12px; border-left: 1px solid #eee;">شماره / نام</th>
                    <th style="padding: 12px; border-left: 1px solid #eee;">وضعیت</th>
                    <th style="padding: 12px;">عملیات (مسدودسازی / پیام شخصی)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; border-left: 1px solid #eee;"><?= $u['id'] ?></td>
                    <td style="padding: 12px; border-left: 1px solid #eee;">
                        <b><?= htmlspecialchars($u['phone']) ?></b>
                    </td>
                    <td style="padding: 12px; border-left: 1px solid #eee;">
                        <?= $u['status'] == 1 ? '<span style="color:green;font-weight:bold;">فعال</span>' : '<span style="color:red;font-weight:bold;">مسدود (در انتظار)</span>' ?>
                    </td>
                    <td style="padding: 12px; display: flex; gap: 10px;">
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="act" value="toggle_status">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="new_status" value="<?= $u['status'] == 1 ? 0 : 1 ?>">
                            <button type="submit" style="background:<?= $u['status']==1?'#dc3545':'#28a745' ?>; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer; font-weight:bold;">
                                <?= $u['status'] == 1 ? 'مسدودسازی' : 'تایید و فعال‌سازی' ?>
                            </button>
                        </form>

                        <button type="button" onclick="openPvModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['phone']) ?>')" style="background:#17a2b8; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer; font-weight:bold;">
                            💬 ارسال پیام شخصی
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="pvModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:white; padding:25px; border-radius:8px; width:400px; max-width:90%; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
            <h3 id="modalTitle" style="margin-top:0; color:#333; border-bottom:1px solid #eee; padding-bottom:10px;">ارسال پیام</h3>
            <form method="POST">
                <input type="hidden" name="act" value="send_pv_modal">
                <input type="hidden" name="user_id" id="modalUserId">
                <textarea name="message" rows="4" placeholder="متن پیام شما..." style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:4px; resize:vertical; outline:none;" required></textarea>
                <div style="display:flex; justify-content:space-between; gap:10px;">
                    <button type="button" onclick="document.getElementById('pvModal').style.display='none'" style="flex:1; padding:10px; background:#6c757d; color:white; border:none; border-radius:4px; cursor:pointer;">انصراف</button>
                    <button type="submit" style="flex:1; padding:10px; background:#007bff; color:white; border:none; border-radius:4px; cursor:pointer;">ارسال دایرکت</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openPvModal(id, name) {
        document.getElementById('modalUserId').value = id;
        document.getElementById('modalTitle').innerText = 'ارسال پیام به ' + name;
        document.getElementById('pvModal').style.display = 'flex';
    }
    </script>
</div>