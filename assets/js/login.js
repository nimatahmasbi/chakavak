/**
 * سیستم لاگین ماژولار و مستقل
 * نسخه: 1.0.0
 */

// تابع مرکزی ارتباط با API
async function apiRequest(action, data = {}) {
    const formData = new FormData();
    formData.append('act', action); // استانداردسازی پارامتر به 'act'
    
    for (const key in data) {
        formData.append(key, data[key]);
    }

    try {
        const response = await fetch('api.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) throw new Error('Network response was not ok');
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        alert('خطا در برقراری ارتباط با سرور. لطفا اتصال اینترنت را بررسی کنید.');
        return null;
    }
}

// --------------------------------------------------
// توابع لاگین (متصل به دکمه‌های HTML)
// --------------------------------------------------

// 1. ارسال کد تایید (OTP)
async function sendOtp() {
    const phoneInput = document.getElementById('phone');
    const phone = phoneInput.value.trim();
    
    if (!phone || phone.length < 10) {
        return alert('لطفا شماره موبایل معتبر وارد کنید');
    }

    const btn = document.querySelector('button[onclick="sendOtp()"]');
    const originalText = btn.innerText;
    
    // حالت لودینگ
    btn.innerText = '⏳'; 
    btn.disabled = true;

    const result = await apiRequest('send_otp', { phone: phone });
    
    // بازگشت به حالت عادی
    btn.innerText = originalText;
    btn.disabled = false;

    if (result && result.status === 'success') {
        document.getElementById('step1').classList.add('hidden');
        document.getElementById('step2').classList.remove('hidden');
        
        // نمایش کد برای تست (در نسخه نهایی حذف شود)
        const otpDisplay = document.getElementById('otpDisplay');
        if(otpDisplay) otpDisplay.innerText = 'کد آزمایشی: ' + result.msg;
        
        console.log("OTP Code:", result.msg);
    } else {
        alert(result ? result.msg : 'خطا در ارسال کد');
    }
}

// 2. بررسی کد تایید
async function verifyOtp() {
    const codeInput = document.getElementById('code');
    const code = codeInput.value.trim();
    
    if (!code || code.length < 4) {
        return alert('کد تایید را کامل وارد کنید');
    }

    const result = await apiRequest('verify_otp', { code: code });

    if (result) {
        if (result.status === 'login') {
            window.location.href = 'dashboard.php';
        } else if (result.status === 'register') {
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('step3').classList.remove('hidden');
        } else {
            alert(result.msg || 'کد وارد شده اشتباه است');
        }
    }
}

// 3. تکمیل ثبت نام
async function register() {
    const fname = document.getElementById('fname').value.trim();
    const lname = document.getElementById('lname').value.trim();
    const uname = document.getElementById('uname').value.trim();
    const pass = document.getElementById('pass').value.trim();

    if (!fname || !lname || !uname || !pass) {
        return alert('لطفا تمام فیلدها را پر کنید');
    }

    const result = await apiRequest('register_complete', {
        fname, lname, uname, pass
    });

    if (result && result.status === 'ok') {
        window.location.href = 'dashboard.php';
    } else {
        alert(result ? result.msg : 'خطا در ثبت نام');
    }
}