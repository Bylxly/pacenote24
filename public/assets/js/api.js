// Basis-Pfad relativ zur Lage dieser Datei ableiten (.../public/assets/js/api.js -> .../public),
// damit es egal ist, in welchem Unterordner die App liegt.
const BASE_URL = new URL('../..', import.meta.url).pathname.replace(/\/+$/, '');
const LOGIN_URL = BASE_URL + '/login.php?status=session_expired';

// Bei abgelaufener/fehlender Session (HTTP 401) automatisch ausloggen.
// Ausnahme: Auth-Endpunkte (Login/Register) - dort bedeutet 401 "falsche
// Zugangsdaten" und muss als normale Fehlermeldung zurückkommen, nicht umleiten.
function handleAuth(res, path) {
    if (res.status === 401 && !path.startsWith('/ajax/auth/')) {
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
        return handleAuth(res, path);
    },

    async post(path, payload = {}) {
        const res = await fetch(BASE_URL + path, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        });
        return handleAuth(res, path);
    }
};
