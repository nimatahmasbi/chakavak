// مسیر فایل PHP نسبت به فایل index.php (که در پوشه ch-admin است)
// چون اسکریپت در ch-admin اجرا می‌شود، باید یک پوشه به عقب (..) برود تا به api برسد
const API_URL = '../api/admin.php'; 

export async function apiCall(action, data = {}) {
    const fd = new FormData();
    fd.append('act', action);
    for (const key in data) {
        fd.append(key, data[key]);
    }

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: fd
        });

        // بررسی خطای HTTP (مثل 404 یا 500)
        if (!response.ok) {
            console.error(`HTTP Error: ${response.status}`);
            return { status: 'error', msg: 'خطای سرور (404/500)' };
        }

        const text = await response.text();
        
        try {
            const json = JSON.parse(text);
            
            // اگر نشست ادمین منقضی شده باشد
            if (json.status === 'error' && json.msg === 'Admin Auth Required') {
                window.location.href = 'login.php';
                return;
            }
            return json;
        } catch (e) {
            console.error('Invalid JSON:', text);
            return { status: 'error', msg: 'پاسخ نامعتبر از سرور' };
        }

    } catch (error) {
        console.error('Network Error:', error);
        return { status: 'error', msg: 'خطای شبکه' };
    }
}