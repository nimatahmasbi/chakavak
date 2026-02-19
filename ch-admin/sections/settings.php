<?php
// English|Persian: Update Settings | بروزرسانی تنظیمات
if ($_POST['act'] == 'update_global_settings') {
    foreach ($_POST['settings'] as $key|کلید => $val|مقدار) {
        $pdo->prepare("UPDATE settings SET s_value = ? WHERE s_key = ?")->execute([$val|مقدار, $key|کلید]);
    }
}

$res = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<div class="p-4">
    <h2 class="text-xl mb-4">تنظیمات سیستم | Global Settings</h2>
    <form method="POST">
        <input type="hidden" name="act" value="update_global_settings">
        
        <div class="mb-3">
            <label>وضعیت سیستم پیامک (SMS Gateway):</label>
            <select name="settings[sms_active]" class="form-control">
                <option value="1" <?= $res['sms_active'] == '1' ? 'selected' : '' ?>>فعال (ارسال واقعی)</option>
                <option value="0" <?= $res['sms_active'] == '0' ? 'selected' : '' ?>>غیرفعال (نمایش کد در کنسول)</option>
            </select>
        </div>

        <div class="mb-3">
            <label>اجازه ثبت‌نام کاربر جدید:</label>
            <select name="settings[registration_enabled]" class="form-control">
                <option value="1" <?= $res['registration_enabled'] == '1' ? 'selected' : '' ?>>فعال</option>
                <option value="0" <?= $res['registration_enabled'] == '0' ? 'selected' : '' ?>>غیرفعال</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">ذخیره تغییرات | Save</button>
    </form>
</div>
