import { state } from './state.js';

export function enc(text) {
    if (!text) return "";
    try {
        // استفاده از کلید جاری (برای چت شخصی ممکن است خالی باشد که مشکلی نیست)
        let key = state.currentKey || ""; 
        return CryptoJS.AES.encrypt(text, key).toString();
    } catch (e) {
        return text;
    }
}

export function dec(cipherText, key) {
    if (!cipherText) return "";
    
    // نکته مهم: اگر key تعریف شده (حتی رشته خالی)، حتما از آن استفاده کن
    // قبلاً اگر خالی بود، می‌رفت سراغ کلید پیش‌فرض که اشتباه بود
    let useKey = (key !== undefined && key !== null) ? key : state.currentKey;
    if (useKey === undefined) useKey = "";

    try {
        let bytes = CryptoJS.AES.decrypt(cipherText, useKey);
        let str = bytes.toString(CryptoJS.enc.Utf8);
        return str || cipherText; // اگر دیکد نشد، متن اصلی را برگردان
    } catch (e) {
        return cipherText;
    }
}