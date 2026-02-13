// توابع ساده برای API
async function api(act, data = {}) {
    const fd = new FormData();
    fd.append('act', act);
    for (let k in data) fd.append(k, data[k]);
    const res = await fetch('api/auth.php', { method: 'POST', body: fd });
    return res.json();
}

let savedPhone = '';

// 1. بررسی شماره
async function checkPhone() {
    const ph = document.getElementById('loginPhone').value;
    if (ph.length < 10) return alert('شماره اشتباه است');
    
    savedPhone = ph;
    const btn = document.querySelector('#step-phone button');
    btn.innerText = '...';
    
    const res = await api('check_phone_status', { phone: ph });
    btn.innerText = 'ادامه';

    if (res.status === 'exist') {
        // کاربر هست -> برو به صفحه رمز
        showStep('password');
    } else if (res.status === 'new_user') {
        // کاربر جدید -> برو به OTP (کد قبلا ارسال شده)
        if(res.otp_debug) alert('Code: ' + res.otp_debug); // فقط برای تست
        document.getElementById('otp-phone-display').innerText = ph;
        showStep('otp');
    } else {
        alert('خطا در برقراری ارتباط');
    }
}

// 2. لاگین با رمز عبور
async function doLoginPassword() {
    const pass = document.getElementById('loginPass').value;
    if(!pass) return alert('رمز را وارد کنید');
    
    const res = await api('login_password', { phone: savedPhone, password: pass });
    if (res.status === 'ok') {
        location.reload();
    } else {
        alert(res.msg || 'رمز اشتباه است');
    }
}

// 3. درخواست OTP (برای فراموشی رمز)
async function switchToOTP() {
    const res = await api('send_otp', { phone: savedPhone });
    if (res.status === 'success') {
        if(res.msg) alert('Code: ' + res.msg); // برای تست
        document.getElementById('otp-phone-display').innerText = savedPhone;
        showStep('otp');
    }
}

// 4. بررسی OTP
async function verifyOtp() {
    const code = document.getElementById('otpCode').value;
    const res = await api('verify_otp', { code: code });
    
    if (res.status === 'login') {
        location.reload();
    } else if (res.status === 'register') {
        showStep('register');
    } else {
        alert(res.msg || 'کد اشتباه است');
    }
}

// 5. تکمیل ثبت نام
async function completeRegister() {
    const d = {
        uname: document.getElementById('regUser').value,
        fname: document.getElementById('regFname').value,
        lname: document.getElementById('regLname').value,
        pass: document.getElementById('regPass').value
    };
    if (!d.uname || !d.pass) return alert('نام کاربری و رمز الزامی است');
    
    const res = await api('register_complete', d);
    if (res.status === 'ok') location.reload();
}

// مدیریت نمایش مراحل
function showStep(id) {
    ['step-phone', 'step-password', 'step-otp', 'step-register'].forEach(s => {
        document.getElementById(s).classList.add('hidden');
    });
    document.getElementById('step-' + id).classList.remove('hidden');
}

function backToPhone() {
    showStep('phone');
    document.getElementById('loginPass').value = '';
    document.getElementById('otpCode').value = '';
}

// اینتر زدن
document.getElementById('loginPhone').addEventListener('keypress', e => { if(e.key==='Enter') checkPhone() });
document.getElementById('loginPass').addEventListener('keypress', e => { if(e.key==='Enter') doLoginPassword() });
document.getElementById('otpCode').addEventListener('keypress', e => { if(e.key==='Enter') verifyOtp() });

// اکسپورت توابع به گلوبال
window.checkPhone = checkPhone;
window.doLoginPassword = doLoginPassword;
window.switchToOTP = switchToOTP;
window.verifyOtp = verifyOtp;
window.completeRegister = completeRegister;
window.backToPhone = backToPhone;