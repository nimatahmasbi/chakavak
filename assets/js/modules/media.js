import { state } from './state.js';
import { sendReq } from './chat.js'; // حالا این تابع وجود دارد و خطا نمی‌دهد

let mediaRecorder;
let audioChunks = [];

// --- انتخاب فایل ---
export function triggerFile(mode) {
    state.isCompress = mode; // 1=عکس, 0=فایل
    document.getElementById('fileInput').click();
    document.getElementById('attachMenu').style.display = 'none';
}

export function sendFile() {
    let f = document.getElementById('fileInput').files[0];
    if (!f) return;
    
    // ارسال فایل (بدون متن)
    sendReq('', f, state.isCompress);
    document.getElementById('fileInput').value = '';
}

// --- ضبط صدا ---
export function startRecord() {
    if (!navigator.mediaDevices) return alert('مرورگر شما پشتیبانی نمی‌کند');

    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            mediaRecorder = new MediaRecorder(stream);
            mediaRecorder.start();
            audioChunks = [];
            
            // افکت دکمه
            let btn = document.querySelector('button[onmousedown*="startRecord"]');
            if(btn) btn.style.color = 'red';

            mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
        })
        .catch(err => alert('دسترسی میکروفون داده نشد'));
}

export function stopRecord() {
    if (mediaRecorder) {
        mediaRecorder.stop();
        
        // حذف افکت
        let btn = document.querySelector('button[onmousedown*="startRecord"]');
        if(btn) btn.style.color = '';

        mediaRecorder.onstop = () => {
            const blob = new Blob(audioChunks, { type: 'audio/webm' });
            // ارسال ویس (mode=2)
            sendReq('', blob, 2);
        };
    }
}

// --- اتصال به Window (حیاتی) ---
window.triggerFile = triggerFile;
window.sendFile = sendFile;
window.startRecord = startRecord;
window.stopRecord = stopRecord;