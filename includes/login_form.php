<div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden animate-scale-in p-8 relative mx-auto my-auto">
    
    <div class="text-center mb-8">
        <img src="assets/img/chakavak.png" class="w-20 h-20 mx-auto mb-4 drop-shadow-md">
        <h1 class="text-2xl font-black text-gray-800 tracking-tight">چکاوک</h1>
        <p class="text-gray-500 text-sm mt-2">پیام‌رسان امن و سریع سازمانی</p>
    </div>

    <div id="step-phone">
        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">شماره موبایل</label>
            <div class="relative">
                <input type="tel" id="loginPhone" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-left ltr focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition font-mono text-lg text-gray-800" placeholder="0912...">
                <span class="absolute left-4 top-3.5 text-gray-400">📱</span>
            </div>
        </div>
        <button onclick="checkPhone()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-200/50 transition transform active:scale-95">
            ادامه
        </button>
    </div>

    <div id="step-password" class="hidden">
        <div class="mb-4">
            <label class="block text-sm font-bold text-gray-700 mb-2">رمز عبور</label>
            <input type="password" id="loginPass" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-center outline-none focus:border-blue-500 transition text-lg text-gray-800" placeholder="******">
        </div>
        
        <button onclick="doLoginPassword()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg mb-4 transition transform active:scale-95">
            ورود به حساب
        </button>

        <div class="text-center">
            <button onclick="switchToOTP()" class="text-blue-500 text-sm hover:underline font-bold">
                فراموشی رمز / ورود با پیامک
            </button>
        </div>
        
        <button onclick="backToPhone()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>
    </div>

    <div id="step-otp" class="hidden">
        <div class="text-center mb-6">
            <span class="text-sm text-gray-500">کد ارسال شده به</span>
            <span id="otp-phone-display" class="font-bold text-gray-800 font-mono dir-ltr">...</span>
        </div>
        
        <div class="mb-6 flex justify-center gap-2 ltr">
            <input type="text" id="otpCode" class="w-32 bg-gray-50 border-2 border-gray-100 rounded-xl px-2 py-3 text-center text-2xl tracking-widest font-mono focus:border-blue-500 outline-none text-gray-800" maxlength="5" placeholder="- - - - -">
        </div>

        <button onclick="verifyOtp()" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-green-200/50 transition transform active:scale-95">
            تایید و ورود
        </button>
        
        <button onclick="backToPhone()" class="w-full mt-3 text-gray-400 text-sm hover:text-gray-600">تغییر شماره</button>
    </div>

    <div id="step-register" class="hidden">
        <h3 class="text-center font-bold text-gray-700 mb-4">تکمیل ثبت نام</h3>
        <div class="space-y-3">
            <div class="flex gap-2">
                <input id="regFname" class="w-1/2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800" placeholder="نام">
                <input id="regLname" class="w-1/2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-800" placeholder="نام خانوادگی">
            </div>
            <input id="regUser" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-left ltr text-gray-800" placeholder="Username">
            <input type="password" id="regPass" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-center text-gray-800" placeholder="رمز عبور">
        </div>
        <button onclick="completeRegister()" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl mt-4 shadow-lg">ساخت حساب</button>
    </div>
</div>