import { API_URL } from './config.js';

export async function apiCall(action, data = {}) {
    const formData = new FormData();
    formData.append('act', action);
    
    for (const key in data) {
        formData.append(key, data[key]);
    }

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error("API Error:", error);
        return { status: 'error', msg: 'خطا در برقراری ارتباط' };
    }
}