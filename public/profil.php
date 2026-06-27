<?php
require_once __DIR__ . '/../app/session/guard.php';
requireAuth();

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <?php include 'head.php'; ?>
</head>

<body>
<?php include 'navbar.php'; ?>

<main class="py-4" style="max-width:640px;margin:0 auto;padding:2rem 1rem;">

  <h1 class="mb-4">Mein <span style="color:var(--accent);">Profil</span></h1>

  <div id="alert" class="alert d-none" role="alert"></div>

  <!-- E-Mail ändern -->
  <div class="card mb-4">
    <div class="card-body p-4">
      <h5 class="mb-3">E-Mail-Adresse</h5>
      <form id="email-form">
        <div class="form-group mb-3">
          <label for="email" class="form-label">E-Mail</label>
          <input type="email" id="email" class="form-control"
                 value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">E-Mail speichern</button>
      </form>
    </div>
  </div>

  <!-- Passwort ändern -->
  <div class="card mb-4">
    <div class="card-body p-4">
      <h5 class="mb-3">Passwort ändern</h5>
      <form id="password-form">
        <div class="form-group mb-3">
          <label for="old-password" class="form-label">Aktuelles Passwort</label>
          <input type="password" id="old-password" class="form-control" required>
        </div>
        <div class="form-group mb-3">
          <label for="new-password" class="form-label">Neues Passwort</label>
          <input type="password" id="new-password" class="form-control" required>
        </div>
        <div class="form-group mb-3">
          <label for="new-password2" class="form-label">Neues Passwort wiederholen</label>
          <input type="password" id="new-password2" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Passwort ändern</button>
      </form>
    </div>
  </div>

  <!-- Account löschen -->
  <div class="card mb-4" style="border-color: rgba(239,68,68,.4) !important;">
    <div class="card-body p-4">
      <h5 class="mb-3" style="color:#ef4444;">Account löschen</h5>
      <p class="text-muted mb-3">Dein Account und deine eigenen Routen werden unwiderruflich gelöscht.</p>
      <button id="delete-btn" class="btn btn-danger">Account löschen</button>
    </div>
  </div>

</main>

<!-- Bestätigungs-Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:var(--navy-mid);border:1px solid var(--border);">
      <div class="modal-header" style="border-color:var(--border);">
        <h5 class="modal-title">Account wirklich löschen?</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Dein Account und alle deine eigenen Routen werden unwiderruflich gelöscht.
        Das kann nicht rückgängig gemacht werden.
      </div>
      <div class="modal-footer" style="border-color:var(--border);">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Endgültig löschen</button>
      </div>
    </div>
  </div>
</div>

<script type="module">
import { api } from './assets/js/api.js';
import { validatePassword } from './assets/js/validation.js';

const USER_ID = <?= (int) $user['user_id'] ?>;
const alertEl = document.getElementById('alert');

function showAlert(msg, ok) {
  alertEl.textContent = msg;
  alertEl.className = 'alert ' + (ok ? 'alert-success' : 'alert-danger');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* E-Mail ändern */
document.getElementById('email-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const email = document.getElementById('email').value.trim();
  try {
    const res = await api.post('/ajax/users/update.php', { id: USER_ID, email });
    showAlert(res.success ? 'E-Mail aktualisiert.' : (res.error ?? 'Aktualisierung fehlgeschlagen.'), res.success);
  } catch (err) {
    showAlert('API-Fehler: ' + err.message, false);
  }
});

/* Passwort ändern */
document.getElementById('password-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const oldPw  = document.getElementById('old-password').value;
  const newPw  = document.getElementById('new-password').value;
  const newPw2 = document.getElementById('new-password2').value;

  if (newPw !== newPw2) { showAlert('Die neuen Passwörter stimmen nicht überein.', false); return; }
  const pwError = validatePassword(newPw);                 // gleiche Regex wie serverseitig
  if (pwError) { showAlert(pwError, false); return; }

  try {
    const res = await api.post('/ajax/auth/change-password.php', {
      old_password: oldPw,
      new_password: newPw,
    });
    if (res.success) {
      showAlert('Passwort geändert.', true);
      e.target.reset();
    } else {
      showAlert(res.error ?? 'Passwortänderung fehlgeschlagen.', false);
    }
  } catch (err) {
    showAlert('API-Fehler: ' + err.message, false);
  }
});

/* Account löschen */
const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
document.getElementById('delete-btn').addEventListener('click', () => deleteModal.show());

document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
  deleteModal.hide();
  try {
    const res = await api.post('/ajax/users/delete.php', { id: USER_ID });
    if (res.success) {

      window.location.href = './home.php';
    } else {
      showAlert(res.error ?? 'Löschen fehlgeschlagen.', false);
    }
  } catch (err) {
    showAlert('API-Fehler: ' + err.message, false);
  }
});
</script>

</body>
</html>
