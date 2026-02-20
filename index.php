<?php 
require __DIR__ . '/ch-admin/config.php'; 

// بررسی دقیق: فقط اگر سشن کاربر وجود داشت و معتبر بود ریدایرکت انجام شود
// Check strictly: Redirect only if valid session exists
if(isset($_SESSION['uid']) && $_SESSION['uid'] > 0) {
    header("Location: dashboard.php");
    exit;
} elseif(isset($_COOKIE['auth_token'])) {
    // پاک کردن کوکی نامعتبر برای جلوگیری از لوپ بی‌نهایت
    // Clear invalid cookie to prevent infinite redirect loop
    setcookie('auth_token', '', time() - 3600, '/');
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
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Vazirmatn', 'sans-serif'] }
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
            height: 100dvh; 
        }
        .hidden { display: none; }
    </style>
</head>
<body>

    <?php require 'includes/login_form.php'; ?>
    <script src="assets/js/login.js?v=3"></script>
    
</body>
</html>
