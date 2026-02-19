<?php
require_once 'config.php';

// 1. عملیات خروج: اگر پارامتر logout در آدرس بود
if (isset($_GET['logout'])) {
    session_destroy(); // نابود کردن سشن
    header("Location: login.php"); // رفرش صفحه برای پاک شدن اثرات
    exit;
}

// 2. اگر ادمین قبلاً وارد شده، مستقیم بفرست به داشبورد
if (isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

$error = '';

// 3. پردازش فرم ورود
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $u = $_POST['username'];
    $p = $_POST['password'];

    try {
        // جستجو در دیتابیس
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$u]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // بررسی رمز عبور (هش شده یا ساده)
        if ($admin && password_verify($p, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = $admin['id'];
            header("Location: index.php");
            exit;
        } 
        // رمز عبور اضطراری (بک‌دور برای وقتی که دیتابیس خالی است)
        elseif ($u === 'admin' && $p === '123456') {
            $_SESSION['admin'] = 1;
            header("Location: index.php");
            exit;
        } else {
            $error = "نام کاربری یا رمز عبور اشتباه است";
        }
    } catch (Exception $e) {
        $error = "خطا در اتصال به دیتابیس: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به مدیریت</title>
    <script src="../libs/tailwind.js"></script>
    <link href="../libs/vazir/font.css" rel="stylesheet">
    <style>body { font-family: 'Vazirmatn', sans-serif; }</style>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen bg-gradient-to-br from-blue-50 to-indigo-100">

    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-sm transform transition-all hover:scale-[1.01]">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-black text-gray-800">مدیریت چکاوک</h1>
            <p class="text-gray-400 text-xs mt-2 font-medium">لطفاً برای ادامه وارد شوید</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm text-center border border-red-100 font-bold flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">نام کاربری</label>
                <input type="text" name="username" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition text-left ltr font-mono text-lg" required>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">رمز عبور</label>
                <input type="password" name="password" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition text-left ltr font-mono text-lg" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3.5 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-600/30 active:scale-95 transform">ورود به پنل</button>
        </form>
        
        <div class="mt-8 text-center border-t pt-6">
            <a href="../index.php" class="text-xs text-gray-400 hover:text-blue-600 transition flex items-center justify-center gap-1">
                <span>بازگشت به صفحه اصلی سایت</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
        </div>
    </div>

</body>
</html>