import { validatePassword } from './validation.js';
import { api } from './api.js';

async function logout() {
    const res = await fetch('./ajax/auth/logout.php', {
        method: 'POST'
    });

    if (res.ok) {
        window.location.href = './login.php';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const login_form = document.getElementById('loginFormular');
    const register_form = document.getElementById('create-form');

    if (login_form) {
        login_form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const email    = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorEl  = document.getElementById('loginError');

            errorEl.classList.add('d-none');

            const data = await api.post('/ajax/auth/login.php', {email, password});

            if (data.success) {
                window.location.href = './home.php';
            } else {
                errorEl.textContent = data.error ?? 'Login fehlgeschlagen';
                errorEl.classList.remove('d-none');
            }
        });
    }

    if (register_form) {
        register_form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorEl = document.getElementById('registerError');

            if (validatePassword(password) != null) {
                errorEl.textContent = validatePassword(password);
                errorEl.classList.remove('d-none');
                return;
            }

            const data = await api.post('/ajax/auth/register.php', {email, password});

            if (data.success) {
                window.location.href = './login.php?status=registered';
            } else {
                errorEl.textContent = data.error ?? 'Registrierung fehlgeschlagen';
                errorEl.classList.remove('d-none');
            }
        });
    }
});

window.logout = logout;