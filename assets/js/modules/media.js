import { apiCall } from './api.js';
import { state } from './state.js';
import { loadMsg } from './chat.js';

let currentAudio = null;
let mediaRecorder = null;
let audioChunks = [];
let recordStartTime = 0;
let recordTimerInterval = null;
let isPaused = false;

// --- پخش ویس (Sticky Player) ---
export function playVoice(url) {
    // اگر قبلی داشت پخش میشد قطع کن
    if (currentAudio) {
        currentAudio.pause();
    }
    
    currentAudio = new Audio(url);
    const player = document.getElementById('stickyPlayer');
    const playBtn = document.getElementById('stickyPlayBtn');
    const progress = document.getElementById('stickyProgress');
    const timeDisplay = document.getElementById('stickyTime');
    
    // نمایش پلیر
    player.classList.remove('hidden');
    playBtn.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>'; // Pause Icon
    
    currentAudio.play();
    
    // آپدیت زمان و پروگرس
    currentAudio.ontimeupdate = () => {
        if (!currentAudio.duration) return;
        const percent = (currentAudio.currentTime / currentAudio.duration) * 100;
        progress.style.width = `${percent}%`;
        
        // فرمت زمان
        const m = Math.floor(currentAudio.currentTime / 60);
        const s = Math.floor(currentAudio.currentTime % 60).toString().padStart(2, '0');
        timeDisplay.innerText = `00:${s}`;
    };
    
    currentAudio.onended = () => {
        playBtn.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>'; // Play Icon
        progress.style.width = '0%';
    };
}

export function toggleStickyPlay() {
    if (!currentAudio) return;
    const playBtn = document.getElementById('stickyPlayBtn');
    
    if (currentAudio.paused) {
        currentAudio.play();
        playBtn.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>';
    } else {
        currentAudio.pause();
        playBtn.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>';
    }
}

export function seekAudio(e) {
    if (!currentAudio || !currentAudio.duration) return;
    const bar = e.currentTarget;
    const clickX = e.offsetX;
    const width = bar.clientWidth;
    const percent = clickX / width;
    currentAudio.currentTime = percent * currentAudio.duration;
}

export function closeStickyPlayer() {
    if (currentAudio) {
        currentAudio.pause();
        currentAudio = null;
    }
    document.getElementById('stickyPlayer').classList.add('hidden');
}

// --- ضبط صدا (کد قبلی) ---
export async function startRecording() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return alert('عدم پشتیبانی');
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];
        isPaused = false;
        mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
        mediaRecorder.start();
        
        document.getElementById('inputArea').classList.add('hidden');
        document.getElementById('voiceArea').classList.remove('hidden');
        
        recordStartTime = Date.now();
        updateTimer();
        recordTimerInterval = setInterval(updateTimer, 1000);
    } catch (e) { alert('دسترسی میکروفون رد شد'); }
}

function updateTimer() {
    if(isPaused) return;
    const diff = Math.floor((Date.now() - recordStartTime) / 1000);
    const m = Math.floor(diff / 60).toString().padStart(2, '0');
    const s = (diff % 60).toString().padStart(2, '0');
    document.getElementById('voiceTimer').innerText = `${m}:${s}`;
}

export function pauseRecording() {
    if (!mediaRecorder) return;
    if (mediaRecorder.state === 'recording') {
        mediaRecorder.pause(); isPaused = true;
        document.getElementById('btnPauseVoice').innerHTML = '▶';
    } else {
        mediaRecorder.resume(); isPaused = false;
        document.getElementById('btnPauseVoice').innerHTML = 'II';
    }
}

export function cancelRecording() {
    if (mediaRecorder) { mediaRecorder.stop(); mediaRecorder.stream.getTracks().forEach(t => t.stop()); }
    resetVoiceUI();
}

export function sendVoice() {
    if (!mediaRecorder) return;
    mediaRecorder.onstop = () => {
        const audioBlob = new Blob(audioChunks, { type: 'audio/mp3' });
        const file = new File([audioBlob], "voice.mp3", { type: "audio/mp3" });
        uploadFile({ files: [file] }, 'voice');
        resetVoiceUI();
    };
    mediaRecorder.stop();
    mediaRecorder.stream.getTracks().forEach(t => t.stop());
}

function resetVoiceUI() {
    clearInterval(recordTimerInterval);
    document.getElementById('voiceArea').classList.add('hidden');
    document.getElementById('inputArea').classList.remove('hidden');
    document.getElementById('voiceTimer').innerText = '00:00';
    document.getElementById('btnPauseVoice').innerHTML = 'II';
}

export function uploadFile(input, type) {
    if (!state.currChat) return;
    const file = input.files ? input.files[0] : input.files[0]; 
    if (!file) return;

    const fd = new FormData();
    fd.append('target_id', state.currChat.id);
    fd.append('type', state.currChat.type);
    fd.append('message', (type === 'voice' ? 'پیام صوتی' : 'فایل'));
    fd.append('is_image', (type === 'image' ? 1 : (type === 'voice' ? 2 : 0)));
    fd.append('file', file);

    const hStatus = document.getElementById('headStatus');
    if(hStatus) hStatus.innerText = 'درحال ارسال...';

    apiCall('send_message', fd, true).then(d => {
        if(hStatus) hStatus.innerText = 'آنلاین'; // یا رفرش
        if (d.status === 'ok') loadMsg(true);
        else alert('خطا در ارسال');
    });
}

window.playVoice = playVoice;
window.toggleStickyPlay = toggleStickyPlay;
window.seekAudio = seekAudio;
window.closeStickyPlayer = closeStickyPlayer;
// توابع ضبط هم اکسپورت شوند...
window.startRecording = startRecording;
window.pauseRecording = pauseRecording;
window.cancelRecording = cancelRecording;
window.sendVoice = sendVoice;
window.uploadFile = uploadFile;