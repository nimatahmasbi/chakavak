<!DOCTYPE html>
<html lang="en" dir="ltr" id="htmlTag">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Chakavak">
    
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/chakavak.png?v=3">
    <link rel="icon" type="image/png" href="assets/img/chakavak.png?v=3">
    
    <title>Chakavak</title>
    <link rel="manifest" href="assets/json/manifest.json?v=2">
    
    <script src="libs/tailwind.js"></script>
    <script src="libs/crypto-js.js"></script>
    
    <link href="libs/vazir/font.css" rel="stylesheet">
    
    <script src="assets/js/lang.js?v=2"></script>
    <link rel="stylesheet" href="assets/css/style.css?v=2">

    <script>
        const MY_ID = <?php echo $_SESSION['uid'] ?? 0; ?>;
        // اصلاح آدرس آواتار پیش‌فرض
        const MY_AVATAR = "<?php echo ($me['avatar'] == 'default' || empty($me['avatar']) ? 'assets/img/chakavak.png' : $me['avatar']); ?>";
    </script>
</head>
<body>
<div id="lightbox" onclick="this.style.display='none'" class="fixed inset-0 bg-black/90 z-[400] hidden items-center justify-center p-4">
    <img id="lightboxImg" class="max-w-full max-h-full object-contain">
</div>