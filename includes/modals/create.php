<div id="createModal" class="hidden fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4">
    <div class="bg-[var(--bg-secondary)] p-6 rounded-2xl w-80 text-[var(--text-primary)] border border-[var(--border-color)]">
        <h3 class="font-bold mb-4 text-lg" id="createTitle">New</h3>
        <input id="createName" class="w-full bg-[var(--bg-primary)] border border-[var(--border-color)] p-3 rounded-xl mb-4 outline-none text-[var(--text-primary)]" placeholder="Name...">
        <div class="flex gap-2">
            <button onclick="closeModal('createModal')" class="flex-1 text-[var(--accent-color)] py-2" data-t="cancel">Cancel</button>
            <button onclick="doCreate()" class="flex-1 bg-[var(--accent-color)] text-white py-2 rounded-lg font-bold" data-t="create">Create</button>
        </div>
    </div>
</div>