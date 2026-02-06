<div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md">
    <div class="text-center mb-6">
        <img src="assets/img/chakavak.png" class="w-28 h-28 mx-auto mb-2 object-contain filter drop-shadow-md">
        
        <h1 class="text-2xl font-bold text-blue-600 mb-2">چکاوک</h1>
        <p class="text-gray-500">پیام‌رسان امن و سریع</p>
    </div>
    
    <div id="step1">
        <input id="phone" class="w-full border p-3 rounded-xl mb-4 text-left dir-ltr outline-none focus:border-blue-500 transition" placeholder="شماره موبایل (09...)">
        <button onclick="sendOtp()" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">ارسال کد</button>
    </div>

    <div id="step2" class="hidden">
        <div class="bg-yellow-50 text-yellow-800 p-3 rounded mb-4 text-center text-sm" id="otpDisplay"></div>
        <input id="code" class="w-full border p-3 rounded-xl mb-4 text-center tracking-widest outline-none focus:border-green-500 transition text-xl" placeholder="- - - - -">
        <button onclick="verifyOtp()" class="w-full bg-green-600 text-white py-3 rounded-xl font-bold hover:bg-green-700 transition shadow-lg shadow-green-500/30">تایید و ورود</button>
    </div>

    <div id="step3" class="hidden">
        <input id="fname" class="w-full border p-3 rounded-xl mb-2 outline-none focus:border-blue-500" placeholder="نام">
        <input id="lname" class="w-full border p-3 rounded-xl mb-2 outline-none focus:border-blue-500" placeholder="نام خانوادگی">
        <input id="uname" class="w-full border p-3 rounded-xl mb-2 text-left dir-ltr outline-none focus:border-blue-500" placeholder="نام کاربری (انگلیسی)">
        <input id="pass" type="password" class="w-full border p-3 rounded-xl mb-4 outline-none focus:border-blue-500" placeholder="رمز عبور">
        <button onclick="register()" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition">تکمیل ثبت نام</button>
    </div>
</div>