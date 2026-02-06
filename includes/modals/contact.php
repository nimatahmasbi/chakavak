<div id="contactModal" class="hidden fixed inset-0 bg-[var(--bg-primary)] z-50 flex flex-col p-4">
    <div class="flex justify-between items-center mb-4">
        <button onclick="closeModal('contactModal')" class="text-[var(--accent-color)]" data-t="cancel">Cancel</button>
        <span class="font-bold text-lg text-[var(--text-primary)]" data-t="nav_contacts">Contacts</span>
        <span></span>
    </div>
    <div class="bg-[var(--bg-secondary)] p-3 rounded-xl mb-4 flex gap-2 items-center border border-[var(--border-color)]">
        <input id="newContactInput" class="bg-transparent border-none outline-none text-[var(--text-primary)] w-full text-sm ltr" data-tp="add_contact_placeholder">
        <button onclick="addNewContact()" class="bg-[var(--accent-color)] text-white p-2 rounded-lg text-xs font-bold whitespace-nowrap" data-t="add_btn">Add</button>
    </div>
    <div id="contactList" class="flex-1 overflow-y-auto space-y-2">Loading...</div>
</div>