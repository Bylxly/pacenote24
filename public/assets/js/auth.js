async function logout() {
    const res = await fetch('./ajax/auth/logout.php', {
        method: 'POST'
    });

    if (res.ok) {
        window.location.href = './login.php';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginFormular');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const email    = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const errorEl  = document.getElementById('loginError');

        errorEl.classList.add('d-none');

        const res = await fetch('./ajax/auth/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });

        const data = await res.json();

        if (res.ok && data.success) {
            window.location.href = './index.php';
        } else {
            errorEl.textContent = data.error ?? 'Login fehlgeschlagen';
            errorEl.classList.remove('d-none');
        }
    });
});