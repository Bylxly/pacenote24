const BASE_URL = 'http://localhost';

export const api = {
    async get(path, query = {}) {
        let url = BASE_URL + path;
        const params = new URLSearchParams(
            Object.entries(query).filter(([, v]) => v !== null && v !== undefined)
        );
        if ([...params].length) url += '?' + params.toString();

        const res = await fetch(url, {
            headers: { 'Accept': 'application/json' }
        });
        return res.json().catch(() => ({ success: false, error: 'Malformed response' }));
    },

    async post(path, payload = {}) {
        const res = await fetch(BASE_URL + path, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        });
        return res.json().catch(() => ({ success: false, error: 'Malformed response' }));
    }
};
