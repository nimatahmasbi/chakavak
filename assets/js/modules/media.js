import { state } from './state.js';
import { sendReq } from './sender.js'; // *** تغییر مهم: حل مشکل ایمپورت ***

let mediaRecorder;
let audioChunks = [];

export function triggerFile(mode) {
    state.isCompress = mode; 
    document.getElementById('fileInput').click();
    document.getElementById('attachMenu').style.display = 'none';
}

export function sendFile() {
    let f = document.getElementById('fileInput').files[0];
    if (!f) return;
    
    sendReq('', f, state.isCompress);
    document.getElementById('fileInput').value = '';
}

export function startRecord() {
    if (!navigator.mediaDevices) return alert('مرورگر پشتیبانی نمی‌کند');

    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            mediaRecorder = new MediaRecorder(stream);
            mediaRecorder.start();
            audioChunks = [];
            
            let btn = document.querySelector('button[onmousedown*="startRecord"]');
            if(btn) btn.style.color = 'red';

            mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
        })
        .catch(err => alert('دسترسی میکروفون رد شد'));
}

export function stopRecord() {
    if (mediaRecorder) {
        mediaRecorder.stop();
        
        let btn = document.querySelector('button[onmousedown*="startRecord"]');
        if(btn) btn.style.color = '';

        mediaRecorder.onstop = () => {
            const blob = new Blob(audioChunks, { type: 'audio/webm' });
            sendReq('', blob, 2);
        };
    }
}

window.triggerFile = triggerFile;
window.sendFile = sendFile;
window.startRecord = startRecord;
window.stopRecord = stopRecord;