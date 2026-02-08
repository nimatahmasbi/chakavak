<div id="userModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50 backdrop-blur-sm">
    </div>

<div id="dmModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50 backdrop-blur-sm">
    <div class="bg-white rounded-xl w-full max-w-md h-[500px] flex flex-col shadow-2xl overflow-hidden">
        <div class="p-3 border-b bg-gray-50 flex justify-between items-center">
            <span class="font-bold text-gray-700">چت با: <span id="dmTargetName" class="text-blue-600"></span></span>
            <button onclick="document.getElementById('dmModal').classList.add('hidden')" class="text-red-500 text-xl px-2 hover:bg-red-50 rounded">✕</button>
        </div>
        <div id="dmHistory" class="flex-1 overflow-y-auto p-4 bg-[#e5ddd5] space-y-2"></div>
        <div class="p-3 border-t bg-white flex gap-2">
            <input type="hidden" id="dmTargetId">
            <input id="dmInput" class="flex-1 border border-gray-300 rounded-full px-4 py-2 outline-none focus:border-blue-500" placeholder="پیام..." autocomplete="off">
            <button onclick="sendDm()" class="bg-blue-600 text-white p-2 rounded-full w-10 h-10 flex items-center justify-center shadow hover:bg-blue-700 transition">➤</button>
        </div>
    </div>
</div>