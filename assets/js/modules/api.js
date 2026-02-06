export async function apiCall(action, data = {}, hasFile = false) {
    let body;
    if (hasFile) {
        body = data; // اگر دیتا فرم‌دیتا است
        body.append('act', action);
    } else {
        body = new FormData();
        body.append('act', action);
        for (let key in data) body.append(key, data[key]);
    }

    try {
        const res = await fetch('api.php', { method: 'POST', body: body });
        if (!res.ok) throw new Error('Network Error');
        return await res.json();
    } catch (e) {
        console.error("API Error:", e);
        return { status: 'error', msg: 'Connection Failed' };
    }
}