<div class="h-[60px] bg-white dark:bg-gray-800 shadow-sm flex items-center px-4 justify-between shrink-0 z-20 relative">
    <div class="flex items-center cursor-pointer" onclick="clickHeader()">
        <button onclick="event.stopPropagation(); closeChat()" class="md:hidden ml-3 text-gray-500 dark:text-gray-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
        </button>
        <img id="headAvatar" src="assets/img/chakavak.png" class="w-10 h-10 rounded-full object-cover border border-gray-100 dark:border-gray-600">
        <div class="mr-3">
            <h4 id="headName" class="font-bold text-gray-800 dark:text-white text-sm">...</h4>
            <span id="headStatus" class="text-xs text-gray-500 dark:text-gray-400">...</span>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <button id="groupSettingsBtn" onclick="clickHeader()" class="hidden text-gray-500 dark:text-gray-400 hover:text-blue-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
        </button>
    </div>
</div>

<div id="stickyPlayer" class="hidden absolute top-[60px] left-0 right-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700 p-2 z-30 shadow-md flex items-center gap-3 animate-slide-down">
    <button onclick="closeStickyPlayer()" class="text-gray-400 hover:text-red-500 p-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
    <button id="stickyPlayBtn" onclick="toggleStickyPlay()" class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center shadow-md hover:bg-blue-700 transition">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
    </button>
    <div class="flex-1 flex flex-col justify-center">
        <span class="text-xs font-bold text-gray-700 dark:text-gray-200 mb-1">پیام صوتی</span>
        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-1.5 cursor-pointer relative" onclick="seekAudio(event)">
            <div id="stickyProgress" class="bg-blue-600 h-1.5 rounded-full w-0 transition-all duration-100"></div>
        </div>
    </div>
    <span id="stickyTime" class="text-[10px] font-mono text-gray-500 w-10 text-center">00:00</span>
</div>

<div class="flex-1 relative w-full overflow-hidden bg-[#efe7dd] dark:bg-gray-900">
    <div class="absolute inset-0 opacity-10 dark:opacity-5 pointer-events-none" style="background-image: url('assets/img/chat-bg.png'); background-size: 400px;"></div>
    <div id="msgBox" class="relative z-10 h-full overflow-y-auto custom-scrollbar p-4 flex flex-col gap-1 pb-4">
        <div class="text-center text-gray-400 text-sm mt-10">...</div>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 p-3 flex items-end gap-2 shadow-[0_-2px_10px_rgba(0,0,0,0.05)] z-20 shrink-0 min-h-[70px] items-center">
    <button onclick="document.getElementById('attachMenu').style.display='flex'" class="text-gray-500 dark:text-gray-400 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
    </button>
    
    <div id="inputArea" class="flex-1 flex items-end gap-2 w-full">
        <textarea id="msgInput" rows="1" class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white rounded-2xl px-4 py-3 outline-none resize-none max-h-32 custom-scrollbar transition-all" placeholder="پیام..." dir="auto"></textarea>
        <button onclick="startRecording()" class="text-gray-500 dark:text-gray-400 p-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
        </button>
        <button onclick="sendText()" class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-full shadow-lg transform active:scale-90 transition">
            <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
        </button>
    </div>

    <div id="voiceArea" class="hidden flex-1 flex items-center justify-between bg-red-50 dark:bg-red-900/20 rounded-2xl px-4 py-2 border border-red-100 dark:border-red-900/30 w-full">
        <div class="flex items-center gap-3">
            <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
            <span id="voiceTimer" class="font-mono text-red-600 dark:text-red-400 font-bold">00:00</span>
            <span id="voiceStatus" class="text-xs text-gray-500 dark:text-gray-400 ml-2">درحال ضبط...</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="cancelRecording()" class="text-gray-400 hover:text-red-500 p-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            <button onclick="pauseRecording()" id="btnPauseVoice" class="text-blue-600 hover:bg-blue-100 p-2 rounded-full font-bold w-8 h-8 flex items-center justify-center">II</button>
            <button onclick="sendVoice()" class="bg-blue-600 text-white p-2 rounded-full shadow-lg hover:bg-blue-700"><svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg></button>
        </div>
    </div>
</div>

<div id="msgMenu" class="hidden fixed bg-white dark:bg-gray-800 shadow-xl rounded-lg border border-gray-100 dark:border-gray-700 z-50 w-40 overflow-hidden py-1">
    <button onclick="deleteMessage()" class="w-full text-right px-4 py-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-500 text-sm flex items-center gap-2"><span>🗑</span> حذف پیام</button>
</div>

<div id="attachMenu" class="hidden absolute bottom-20 right-4 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 p-2 flex-col gap-2 z-30">
    <label class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg cursor-pointer text-gray-700 dark:text-gray-200"><span>📷</span> عکس<input type="file" hidden accept="image/*" onchange="uploadFile(this, 'image')"></label>
    <label class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg cursor-pointer text-gray-700 dark:text-gray-200"><span>📁</span> فایل<input type="file" hidden onchange="uploadFile(this, 'file')"></label>
</div>