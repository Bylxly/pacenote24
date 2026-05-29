<?php
require_once("../../app/session/guard.php");
requireAdmin();
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nutzer anlegen</title>
  <link rel="stylesheet" href="../assets/css/stylesheetmain.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<header class="admin-header">
  <span class="admin-header__badge">Admin</span>
  <nav class="admin-header__breadcrumb">
    <a href="adminpanel.php">User Management</a>
    <span>›</span>
    <strong>Nutzer anlegen</strong>
  </nav>
</header>

<main class="admin-main">

  <div id="banner-success" class="banner banner--success"></div>
  <div id="banner-error"   class="banner banner--error"></div>

  <div class="card">
    <div class="card__header">
      <span class="card__title">Neuen Nutzer erstellen</span>
    </div>
    <div class="card__body">
      <form id="create-form">

        <div class="form-group">
          <label for="email">E-Mail Adresse</label>
          <input type="email" id="email" name="email" placeholder="nutzer@beispiel.de" required>
        </div>

        <div class="form-group">
          <label for="password">Passwort</label>
          <input type="password" id="password" name="password" placeholder="Mindestens 8 Zeichen + Sonderzeichen" required>
        </div>

        <button type="submit" class="btn btn-primary">Nutzer anlegen</button>

      </form>
    </div>
  </div>

</main>

<script type="module">
import { api } from '../assets/js/api.js';

const form          = document.getElementById('create-form');
const bannerSuccess = document.getElementById('banner-success');
const bannerError   = document.getElementById('banner-error');

function showSuccess(msg) {
  bannerSuccess.textContent = msg;
  bannerSuccess.classList.add('visible');
  bannerError.classList.remove('visible');
}

function showError(msg) {
  bannerError.textContent = msg;
  bannerError.classList.add('visible');
  bannerSuccess.classList.remove('visible');
}

function validatePassword(password) {
  if (password.length < 8)            return 'Passwort muss mindestens 8 Zeichen lang sein.';
  if (!/[^A-Za-z0-9]/.test(password)) return 'Passwort muss mindestens ein Sonderzeichen enthalten.';
  return null;
}

form.addEventListener('submit', async (e) => {
  e.preventDefault();


  const email    = form.email.value.trim();
  const password = form.password.value;

  if (!email || !password) {
    showError('E-Mail und Passwort sind erforderlich.');
    return;
  }

  const pwError = validatePassword(password);
  if (pwError) { showError(pwError); return; }

  try {
    const createResponse = await api.post('/ajax/users/create.php', { email, password });

    if (createResponse.success && createResponse.user_id) {
      showSuccess(`Nutzer erfolgreich angelegt (ID: ${parseInt(createResponse.user_id)}).`);
      form.reset();
    } else {
      showError(createResponse.error ?? 'Unbekannter Fehler.');
    }
  } catch (e) {
    showError('API nicht erreichbar.');
  }
});
</script>

</body>
</html>