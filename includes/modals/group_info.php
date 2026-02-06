<div id="groupInfoModal" class="hidden fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4">
    <div class="bg-[var(--bg-secondary)] rounded-xl w-full max-w-sm overflow-hidden text-[var(--text-primary)] border border-[var(--border-color)]">
        <div class="p-4 relative text-center border-b border-[var(--border-color)]">
            <button onclick="closeModal('groupInfoModal')" class="absolute top-4 right-4 text-[var(--accent-color)]">Done</button>
            <img id="gInfoAvatar" class="w-20 h-20 rounded-full mx-auto mb-2 object-cover border-2 border-gray-600">
            <h2 id="gInfoName" class="font-bold text-xl"></h2>
            <p id="gInfoCount" class="text-sm text-[var(--text-secondary)]"></p>
            <div id="adminActions" class="hidden mt-2 flex justify-center gap-2">
                <button onclick="toggleEditGroup()" class="text-[var(--accent-color)] text-xs bg-gray-800 px-2 py-1 rounded">Edit</button>
            </div>
        </div>
        <div id="editGroupBox" class="p-4 bg-black hidden">
            <input id="editGName" class="w-full bg-[var(--bg-secondary)] border border-gray-600 p-2 rounded text-[var(--text-primary)] mb-2" placeholder="Name">
            <input type="file" id="editGFile" class="text-xs text-[var(--text-secondary)]">
            <button onclick="saveGroupEdit()" class="bg-blue-600 text-white w-full py-2 rounded mt-2">Save</button>
        </div>
        <div id="addMemberBox" class="p-3 border-b border-[var(--border-color)] hidden">
            <div class="flex gap-2">
                <input id="addInput" class="bg-[var(--bg-secondary)] border border-[var(--border-color)] rounded p-2 text-sm flex-1 text-[var(--text-primary)]" placeholder="Add Member">
                <button onclick="addMember()" class="text-[var(--accent-color)] font-bold px-2" data-t="add_member">Add</button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-3 h-64" id="gMembersList"></div>
    </div>
</div>