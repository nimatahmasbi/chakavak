<?php
// ماژول تنظیمات سیستم داشبورد
// System Settings Module
if (!defined('MASTER_SECRET')) { exit; }

// پردازش ذخیره تنظیمات
// Process saving settings
if (isset($_POST['act']) && $_POST['act'] == 'save_settings') {
    foreach ($_POST['cfg'] as $k => $v) {
        $pdo->prepare("UPDATE settings SET s_value=? WHERE s_key=?")->execute([$v, $k]);
    }
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>✅ تنظیمات با موفقیت ذخیره شد.</div>";
}

// دریافت تنظیمات فعلی
// Fetch current settings
$s = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div style="padding: 20px; font-family: Tahoma, 'Vazirmatn', sans-serif; direction: rtl; text-align: right; background: #f9f9f9; min-height: 100vh;">
    
    <h2 style="margin-bottom: 20px; color: #333; font-size: 24px; border-bottom: 2px solid #ddd; padding-bottom: 10px;">⚙️ پیکربندی سیستم (Settings)</h2>

    <div style="background: white; padding: 30px; border: 1px solid #e0e0e0; border-radius: 8px; max-width: 600px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <form method="POST">
            <input type="hidden" name="act" value="save_settings">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #555;">وضعیت سیستم پیامک (SMS OTP):</label>
                <p style="color: #888; font-size: 13px; margin-bottom: 10px;">اگر غیرفعال باشد، کد تایید جهت تست در خروجی مرورگر نمایش داده می‌شود.</p>
                <select name="cfg[sms_active]" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; outline: none; background: #fff;">
                    <option value="1" <?= (isset($s['sms_active']) && $s['sms_active']=='1') ? 'selected' : '' ?>>فعال (ارسال پیامک واقعی)</option>
                    <option value="0" <?= (isset($s['sms_active']) && $s['sms_active']=='0') ? 'selected' : '' ?>>غیرفعال (حالت تست - نمایش کد)</option>
                </select>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #555;">ثبت‌نام کاربران جدید:</label>
                <p style="color: #888; font-size: 13px; margin-bottom: 10px;">با بستن این بخش، فقط ادمین می‌تواند کاربر جدید ایجاد کند.</p>
                <select name="cfg[registration_enabled]" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; outline: none; background: #fff;">
                    <option value="1" <?= (isset($s['registration_enabled']) && $s['registration_enabled']=='1') ? 'selected' : '' ?>>آزاد (همه می‌توانند ثبت‌نام کنند)</option>
                    <option value="0" <?= (isset($s['registration_enabled']) && $s['registration_enabled']=='0') ? 'selected' : '' ?>>بسته (غیرفعال)</option>
                </select>
            </div>

            <button type="submit" style="width: 100%; padding: 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; transition: background 0.3s;">
                ذخیره تغییرات
            </button>
        </form>
    </div>
</div>
