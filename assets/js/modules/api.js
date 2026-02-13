const API_URL = 'api.php';

export async function apiCall(action, data = {}, hasFile = false) {
    let body;
    if (hasFile) {
        body = data;
        body.append('act', action);
    } else {
        body = new FormData();
        body.append('act', action);
        for (const key in data) {
            body.append(key, data[key]);
        }
    }

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: body
        });

        // اگر سرور ارور 500 یا 404 داد
        if (!response.ok) {
            console.error(`HTTP Error: ${response.status}`);
            return { status: 'error', msg: 'Server Error' };
        }

        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error("Invalid JSON:", text);
            return { status: 'error', msg: 'Invalid JSON Response' };
        }

    } catch (error) {
        console.error("Network/API Error:", error);
        return { status: 'error', msg: 'Connection Failed', network_error: true };
    }
}