// آدرس API
const API_URL = 'api.php';

export async function apiCall(action, data = {}, hasFile = false) {
    let body;
    
    if (hasFile) {
        // اگر فرم دیتا از قبل آماده شده است (برای آپلود فایل)
        body = data;
        body.append('act', action);
    } else {
        // تبدیل داده‌های معمولی به فرم دیتا
        body = new FormData();
        body.append('act', action);
        for (const key in data) {
            body.append(key, data[key]);
        }
    }

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: body
        });

        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status}`);
        }

        // تلاش برای پارس کردن جیسون
        const json = await response.json();
        return json;

    } catch (error) {
        console.error("API Error:", error);
        
        // نکته مهم: بازگرداندن یک آبجکت خطا به جای پرتاب ارور
        // این باعث می‌شود برنامه کرش نکند و UI بتواند خطا را نمایش دهد
        return { 
            status: 'error', 
            msg: 'خطا در برقراری ارتباط با سرور (اینترنت خود را چک کنید)',
            network_error: true 
        };
    }
}