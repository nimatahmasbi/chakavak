<?php 
// استفاده از __DIR__ برای اطمینان از مسیر صحیح
require __DIR__ . '/ch-admin/config.php'; 

// اگر کاربر لاگین است، مستقیم به داشبورد برود
if(isset($_SESSION['uid']) || isset($_COOKIE['auth_token'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ورود به چکاوک</title>
    
    <link rel="manifest" href="assets/json/manifest.json">
    
    <link rel="icon" type="image/png" href="assets/img/chakavak.png">
    
    <link href="libs/vazir/font.css" rel="stylesheet">
    
    <script src="libs/tailwind.js"></script>
    <script>
        // تنظیمات تیلویند برای جلوگیری از برخی وارنینگ‌ها
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Vazirmatn', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100dvh; /* ارتفاع داینامیک برای موبایل */
        }
        /* مخفی کردن المان‌ها با انیمیشن */
        .hidden { display: none; }
    </style>
</head>
<body>

    <?php require 'includes/login_form.php'; ?>

    <script src="assets/js/login.js?v=2"></script>
    
</body>
</html>