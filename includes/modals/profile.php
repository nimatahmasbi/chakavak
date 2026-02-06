<div id="publicProfileModal" class="hidden fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4">
    <div class="bg-[var(--bg-secondary)] rounded-xl w-full max-w-sm overflow-hidden text-[var(--text-primary)]">
        <div class="h-24 bg-gray-800 relative">
            <button onclick="closeModal('publicProfileModal')" class="absolute top-2 right-2 bg-black/50 rounded-full p-1">✕</button>
        </div>
        <div class="px-6 pb-6 -mt-12 text-center relative">
            <img id="pubAvatar" class="w-24 h-24 rounded-full border-4 border-black mx-auto bg-gray-700 object-cover">
            <h2 id="pubName" class="font-bold text-xl mt-2"></h2>
            <p id="pubUser" class="text-gray-400 text-sm mb-4"></p>
            <div id="pubBio" class="bg-black p-3 rounded-lg text-sm text-gray-300 mb-4 border border-gray-800"></div>
            <div class="grid grid-cols-4 gap-4 mb-4" id="pubSocials"></div>
            <button onclick="startDirectFromProfile()" class="w-full bg-blue-600 text-white py-2 rounded-lg font-bold">Send Message</button>
        </div>
    </div>
</div>