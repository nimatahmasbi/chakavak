<?php
// ماژول تنظیمات پیشرفته سیستم
// Advanced System Settings Module
if (!defined('MASTER_SECRET')) { exit; }

// پردازش ذخیره تنظیمات
if (isset($_POST['act']) && $_POST['act'] == 'save_settings') {
    foreach ($_POST['cfg'] as $k => $v) {
        // استفاده از INSERT ON DUPLICATE KEY برای اطمینان از ذخیره تنظیمات جدید
        $pdo->prepare("INSERT INTO settings (s_key, s_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE s_value = VALUES(s_value)")->execute([$k, $v]);
    }
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>✅ تنظیمات با موفقیت ذخیره شد.</div>";
}

$s = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div style="padding: 20px; font-family: Tahoma, 'Vazirmatn', sans-serif; direction: rtl; text-align: right; background: #f9f9f9; min-height: 100vh;">
    <h2 style="margin-bottom: 20px; color: #333; font-size: 24px; border-bottom: 2px solid #ddd; padding-bottom: 10px;">⚙️ پیکربندی سیستم (Settings)</h2>

    <div style="background: white; padding: 30px; border: 1px solid #e0e0e0; border-radius: 8px; max-width: 800px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <form method="POST">
            <input type="hidden" name="act" value="save_settings">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">بازیابی رمز عبور:</label>
                    <select name="cfg[forgot_password_enabled]" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                        <option value="1" <?= (isset($s['forgot_password_enabled']) && $s['forgot_password_enabled']=='1') ? 'selected' : '' ?>>فعال</option>
                        <option value="0" <?= (isset($s['forgot_password_enabled']) && $s['forgot_password_enabled']=='0') ? 'selected' : '' ?>>غیرفعال</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">تایید دو مرحله‌ای (Google Auth):</label>
                    <select name="cfg[google_auth_enabled]" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                        <option value="1" <?= (isset($s['google_auth_enabled']) && $s['google_auth_enabled']=='1') ? 'selected' : '' ?>>فعال</option>
                        <option value="0" <?= (isset($s['google_auth_enabled']) && $s['google_auth_enabled']=='0') ? 'selected' : '' ?>>غیرفعال</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">ورود با Passkey:</label>
                    <select name="cfg[passkey_enabled]" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                        <option value="1" <?= (isset($s['passkey_enabled']) && $s['passkey_enabled']=='1') ? 'selected' : '' ?>>فعال</option>
                        <option value="0" <?= (isset($s['passkey_enabled']) && $s['passkey_enabled']=='0') ? 'selected' : '' ?>>غیرفعال</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">وضعیت کاربران جدید:</label>
                    <select name="cfg[require_admin_approval]" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                        <option value="1" <?= (isset($s['require_admin_approval']) && $s['require_admin_approval']=='1') ? 'selected' : '' ?>>مسدود (نیاز به تایید مدیر)</option>
                        <option value="0" <?= (isset($s['require_admin_approval']) && $s['require_admin_approval']=='0') ? 'selected' : '' ?>>فعال (آزاد)</option>
                    </select>
                </div>
            </div>

            <hr style="margin: 25px 0; border: 0; border-top: 1px solid #eee;">

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px; color:#0056b3;">پیام خوش‌آمدگویی (درحال انتظار):</label>
                <p style="font-size:12px; color:#666;">وقتی کاربر ثبت‌نام می‌کند و حسابش نیاز به تایید دارد، این پیام به او دایرکت می‌شود.</p>
                <textarea name="cfg[welcome_message]" rows="3" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;"><?= htmlspecialchars($s['welcome_message'] ?? 'کاربر گرامی، حساب شما ایجاد شد. لطفاً تا تایید توسط مدیریت صبور باشید.') ?></textarea>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px; color:#28a745;">پیام تایید حساب (فعال‌سازی):</label>
                <p style="font-size:12px; color:#666;">پس از اینکه مدیر حساب کاربر را از حالت مسدود خارج کند، این پیام برایش ارسال می‌گردد.</p>
                <textarea name="cfg[activation_message]" rows="3" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;"><?= htmlspecialchars($s['activation_message'] ?? 'حساب شما توسط مدیریت فعال شد. هم‌اکنون می‌توانید از سیستم استفاده کنید.') ?></textarea>
            </div>

            <button type="submit" style="width: 100%; padding: 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold;">
                ذخیره تنظیمات
            </button>
        </form>
    </div>
</div>