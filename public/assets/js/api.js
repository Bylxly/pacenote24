const BASE_URL = 'http://localhost/public';
const LOGIN_URL = BASE_URL + '/login.php?status=session_expired';

// Bei abgelaufener/fehlender Session (HTTP 401) automatisch ausloggen
function handleAuth(res) {
    if (res.status === 401) {
        window.location.href = LOGIN_URL;
        // Promise, das nie auflöst: verhindert Weiterverarbeitung während des Redirects
        return new Promise(() => {});
    }
    return res.json().catch(() => ({ success: false, error: 'Malformed response' }));
}

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
        return handleAuth(res);
    },

    async post(path, payload = {}) {
        const res = await fetch(BASE_URL + path, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        });
        return handleAuth(res);
    }
};
