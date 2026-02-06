import { state } from './state.js';

export function enc(text) {
    if (!text) return "";
    try {
        // از کلید جاری استفاده کن، اگر نبود خطا نده
        let key = state.currentKey || "default_secret"; 
        return CryptoJS.AES.encrypt(text, key).toString();
    } catch (e) {
        return text;
    }
}

export function dec(cipherText, key) {
    if (!cipherText) return "";
    // اولویت با کلید پاس داده شده است، بعد کلید جاری state
    let finalKey = key || state.currentKey || "default_secret";
    try {
        let bytes = CryptoJS.AES.decrypt(cipherText, finalKey);
        let str = bytes.toString(CryptoJS.enc.Utf8);
        return str || cipherText; // اگر خالی شد (خطای دیکد)، خود متن را نشان بده
    } catch (e) {
        return cipherText;
    }
}