<?php
// صفحه اصلی داشبورد مدیریت
// Main Admin Dashboard
if (!defined('MASTER_SECRET')) { exit; }

$adminId = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetchColumn() ?: 1;

// پردازش فرم اعلان همگانی
if (isset($_POST['act']) && $_POST['act'] == 'send_global_announce') {
    $msg = "📢 اعلان مدیریت:\n" . htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');
    $activeUsers = $pdo->query("SELECT id FROM users WHERE status=1")->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, target_id, type, message, created_at) VALUES (?, ?, 'pv', ?, NOW())");
    foreach ($activeUsers as $uid) {
        $stmt->execute([$adminId, $uid, $msg]);
    }
    echo "<div style='background:#cce5ff; color:#004085; padding:15px; margin:20px; border-radius:5px;'>📢 اعلان به تمام کاربران فعال ارسال شد.</div>";
}
?>

<div style="padding: 20px; font-family: Tahoma, 'Vazirmatn', sans-serif; direction: rtl; text-align: right;">
    <h2>خوش آمدید!</h2>
    
    <div style="background: white; padding: 25px; border: 1px solid #007bff; border-radius: 8px; margin-top: 30px; border-right: 5px solid #007bff; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; color: #007bff;">📢 ارسال اعلان همگانی (Broadcasting)</h3>
        <p style="color: #666; font-size: 14px; margin-bottom: 15px;">با نوشتن در این کادر، پیام شما به دایرکت تمام کاربرانِ تایید شده ارسال می‌گردد.</p>
        
        <form method="POST" style="display: flex; gap: 15px; align-items: flex-start;">
            <input type="hidden" name="act" value="send_global_announce">
            <textarea name="message" required placeholder="متن اعلان خود را اینجا بنویسید..." style="flex: 1; padding: 15px; border: 1px solid #ccc; border-radius: 4px; min-height: 100px; outline: none; resize: vertical;"></textarea>
            <button type="submit" style="padding: 15px 30px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; height: 100%; font-weight: bold;">ارسال به همه</button>
        </form>
    </div>
</div>