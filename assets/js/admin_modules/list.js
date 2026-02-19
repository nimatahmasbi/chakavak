import { apiCall } from './api.js';

export function loadDashboardData() {
    apiCall('admin_get_lists').then(d => {
        // لاگ برای دیباگ (در کنسول مرورگر F12 ببینید)
        console.log('Admin Data:', d);

        if (d && d.status === 'ok') {
            // اطمینان از اینکه متغیرها وجود دارند
            const users = d.users || [];
            const groups = d.groups || [];
            
            // بروزرسانی آمار
            updateStat('totalUsers', users.length);
            
            const gCount = groups.filter(g => g.type === 'group').length;
            const cCount = groups.filter(g => g.type === 'channel').length;
            
            updateStat('totalGroups', gCount);
            updateStat('totalChannels', cCount);
            
            // اگر در صفحه کاربران هستیم، جدول را پر کن
            if (window.renderUsersTable && document.getElementById('usersTable')) {
                window.renderUsersTable(users);
            }
            
            // اگر در صفحه گروه‌ها هستیم
            if (window.renderGroupsTable && document.getElementById('groupsTable')) {
                window.renderGroupsTable(groups);
            }
        } else {
            console.error('API Error:', d);
            if(d.msg === 'Admin Auth Required') {
                window.location.href = 'login.php';
            }
        }
    }).catch(err => {
        console.error('Fetch Error:', err);
    });
}

function updateStat(id, value) {
    const el = document.getElementById(id);
    if (el) el.innerText = value;
}