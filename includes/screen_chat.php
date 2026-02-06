<div id="screen-chat" class="screen fixed inset-0 bg-[var(--bg-primary)] z-50 hidden transition-transform duration-300">
    <div class="pt-safe pb-2 px-2 bg-[var(--bg-secondary)] flex items-center shadow border-b border-[var(--border-color)] relative z-20">
        <button onclick="closeChat()" class="ml-1 text-[var(--accent-color)] flex items-center text-xl p-2 rounded-full hover:bg-[var(--bg-primary)] transition">‹</button>
        
        <div class="flex-1 flex flex-col items-start px-2 cursor-pointer" onclick="clickHeader()">
            <div class="flex items-center gap-2">
                <img id="headAvatar" class="w-9 h-9 rounded-full bg-gray-300 object-cover border border-[var(--border-color)]">
                <span class="font-bold text-[var(--text-primary)] text-base truncate" id="headName"></span>
            </div>
            <span class="text-xs text-[var(--text-secondary)] px-11 -mt-1" id="headStatus">...</span>
        </div>
        
        <button id="groupSettingsBtn" onclick="clickHeader()" class="text-[var(--accent-color)] px-2 hidden hover:bg-[var(--bg-primary)] rounded-full p-2 transition">⚙️</button>
    </div>

    <div id="chat-bg"></div>

    <div id="msgBox" class="flex-1 overflow-y-auto p-3 scroll-hide" style="padding-bottom:80px;"></div>

    <div class="bg-[var(--bg-secondary)] p-2 flex gap-2 items-end absolute bottom-0 w-full z-30 pb-safe border-t border-[var(--border-color)] transition-all" id="inputArea">
        <button onclick="document.getElementById('attachMenu').style.display='flex'" class="text-gray-500 p-3 mb-1 transform rotate-45 hover:text-[var(--accent-color)] transition">📎</button>
        
        <div id="attachMenu" class="hidden absolute bottom-16 left-2 bg-[var(--bg-secondary)] shadow-xl rounded-xl border border-[var(--border-color)] p-2 flex-col gap-2 z-50 w-40">
            <button onclick="triggerFile(1)" class="flex items-center gap-3 p-2 hover:bg-[var(--bg-primary)] rounded transition text-sm text-[var(--text-primary)]">
                <span>🖼️</span> <span data-t="photo">Photo</span>
            </button>
            <button onclick="triggerFile(0)" class="flex items-center gap-3 p-2 hover:bg-[var(--bg-primary)] rounded transition text-sm text-[var(--text-primary)]">
                <span>📁</span> <span data-t="file">File</span>
            </button>
            <button onmousedown="startRecord()" onmouseup="stopRecord()" ontouchstart="startRecord()" ontouchend="stopRecord()" class="flex items-center gap-3 p-2 hover:bg-[var(--bg-primary)] rounded transition text-sm text-red-500">
                <span>🎤</span> <span data-t="voice_rec">Voice</span>
            </button>
        </div>

        <input type="file" id="fileInput" class="hidden" onchange="sendFile()">
        
        <textarea id="msgInput" rows="1" class="flex-1 bg-[var(--bg-primary)] rounded-2xl px-4 py-3 outline-none text-[var(--text-primary)] border border-[var(--border-color)] resize-none overflow-hidden scroll-hide" style="min-height:48px; max-height:120px;" data-tp="send_msg" placeholder="Message..."></textarea>
        
        <button onclick="sendText()" class="text-[var(--accent-color)] p-3 mb-1 hover:scale-110 transition">➤</button>
    </div>

    <div id="recordingArea" class="hidden absolute bottom-0 w-full bg-[#1e1e1e] p-2 flex items-center justify-between z-40 pb-safe border-t border-gray-800 h-[80px]">
        <div class="flex items-center gap-3 pl-4 flex-1">
            <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.6)]"></div>
            <span id="recordTimer" class="text-white font-mono text-xl font-bold tracking-widest">0:00,00</span>
        </div>

        <div class="flex items-center gap-2 text-gray-400 text-sm flex-1 justify-center opacity-70">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            <span data-t="cancel">Slide to cancel</span>
        </div>

        <div class="flex-1 flex justify-end pr-2">
            <button onmouseup="stopRecord()" ontouchend="stopRecord()" class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center -mt-6 shadow-xl border-4 border-[#1e1e1e] relative z-50 transform active:scale-95 transition-transform">
                <svg class="w-8 h-8 text-white animate-bounce" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
            </button>
        </div>
    </div>
</div>

<div id="msgMenu" class="context-menu hidden fixed bg-[var(--bg-secondary)] shadow-xl rounded-xl border border-[var(--border-color)] z-[100] overflow-hidden w-40">
    <button onclick="doReply()" class="w-full text-left p-3 hover:bg-[var(--bg-primary)] text-sm flex gap-2 text-[var(--text-primary)] transition">
        <span>↩️</span> Reply
    </button>
    <button onclick="copyText()" class="w-full text-left p-3 hover:bg-[var(--bg-primary)] text-sm flex gap-2 text-[var(--text-primary)] transition">
        <span>📋</span> Copy
    </button>
    <button onclick="viewUserProfile()" class="w-full text-left p-3 hover:bg-[var(--bg-primary)] text-sm flex gap-2 text-[var(--text-primary)] border-t border-[var(--border-color)] transition">
        <span>👤</span> Profile
    </button>
</div>