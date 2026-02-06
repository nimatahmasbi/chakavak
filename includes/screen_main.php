<div id="screen-main" class="screen">
    <div class="pt-safe px-4 py-3 bg-[var(--bg-primary)] flex justify-between items-center z-20 border-b border-[var(--border-color)]">
        <button onclick="toggleEditMode()" class="text-[var(--accent-color)] text-sm font-bold" data-t="edit">Edit</button>
        
        <div class="flex items-center gap-2">
            <img src="assets/img/chakavak.png" class="w-8 h-8 object-contain">
            <span class="font-bold text-lg text-[var(--text-primary)]" data-t="app_name">Chakavak</span>
        </div>
        
        <div class="relative">
            <button onclick="document.getElementById('addMenu').classList.toggle('hidden')" class="text-[var(--accent-color)]">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </button>
            <div id="addMenu" class="hidden absolute top-10 end-0 bg-[var(--bg-secondary)] w-48 rounded-xl shadow-xl border border-[var(--border-color)] overflow-hidden z-50 flex-col">
                <button onclick="openModal('contactModal');document.getElementById('addMenu').classList.add('hidden')" class="p-3 hover:bg-[var(--bg-primary)] text-start flex gap-2 items-center border-b border-[var(--border-color)] text-[var(--text-primary)]">
                    👤 <span data-t="new_contact">Contact</span>
                </button>
                <button onclick="openCreateModal('group');document.getElementById('addMenu').classList.add('hidden')" class="p-3 hover:bg-[var(--bg-primary)] text-start flex gap-2 items-center border-b border-[var(--border-color)] text-[var(--text-primary)]">
                    👥 <span data-t="new_group">Group</span>
                </button>
                <button onclick="openCreateModal('channel');document.getElementById('addMenu').classList.add('hidden')" class="p-3 hover:bg-[var(--bg-primary)] text-start flex gap-2 items-center text-[var(--text-primary)]">
                    📢 <span data-t="new_channel">Channel</span>
                </button>
            </div>
        </div>
    </div>

    <div class="px-3 py-2 bg-[var(--bg-primary)]">
        <div class="bg-[var(--bg-secondary)] rounded-xl flex items-center p-2 h-10 border border-transparent focus-within:border-[var(--accent-color)] transition-colors">
            <svg class="w-5 h-5 text-[var(--text-secondary)] mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input type="text" data-tp="search_placeholder" class="bg-transparent border-none outline-none text-[var(--text-primary)] w-full text-sm placeholder-gray-500" placeholder="Search">
        </div>
    </div>

    <div class="flex gap-4 px-4 pb-0 bg-[var(--bg-primary)] overflow-x-auto scroll-hide text-sm border-b border-[var(--border-color)]">
        <button id="tab-all" onclick="filterChats('all')" class="tab-btn pb-2 font-bold active-tab border-b-2 border-[var(--accent-color)] text-[var(--text-primary)] transition-colors" data-t="tab_all">All</button>
        <button id="tab-personal" onclick="filterChats('personal')" class="tab-btn pb-2 text-[var(--text-secondary)] font-medium transition-colors" data-t="tab_personal">Personal</button>
        <button id="tab-group" onclick="filterChats('group')" class="tab-btn pb-2 text-[var(--text-secondary)] font-medium transition-colors" data-t="tab_groups">Groups</button>
        <button id="tab-channel" onclick="filterChats('channel')" class="tab-btn pb-2 text-[var(--text-secondary)] font-medium transition-colors" data-t="tab_channels">Channels</button>
    </div>

    <div id="chatList" class="flex-1 overflow-y-auto bg-[var(--bg-primary)] pb-20 pt-1"></div>

    <div class="bg-[var(--bg-secondary)] fixed bottom-0 w-full pb-safe pt-2 px-6 flex justify-between items-center z-40 text-[10px] border-t border-[var(--border-color)]">
        <div onclick="openModal('contactModal')" class="flex flex-col items-center text-[var(--text-secondary)] gap-1 cursor-pointer hover:text-[var(--accent-color)] transition">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            <span data-t="nav_contacts">Contacts</span>
        </div>
        <div onclick="loadChats()" class="flex flex-col items-center text-[var(--accent-color)] gap-1 cursor-pointer">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
            <span data-t="nav_chats">Chats</span>
        </div>
        <div onclick="openSettings()" class="flex flex-col items-center text-[var(--text-secondary)] gap-1 cursor-pointer hover:text-[var(--accent-color)] transition">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
            <span data-t="nav_settings">Settings</span>
        </div>
    </div>
</div>