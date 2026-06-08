<?php
$adminTitle      = 'User Detail';
$adminBreadcrumb = [
  ['label' => 'User Management',  'href' => 'adminpanel.php'],
  ['label' => 'Profil verwalten', 'href' => null],
];
require __DIR__ . '/_header.php';
?>

<main class="admin-main">

  <div id="banner-success" class="banner banner--success"></div>
  <div id="banner-error"   class="banner banner--error"></div>

  <div id="page-content"></div>

</main>

<script type="module">
import { api } from '../assets/js/api.js';
import { validatePassword } from '../assets/js/validation.js';

const bannerSuccess = document.getElementById('banner-success');
const bannerError   = document.getElementById('banner-error');
const pageContent   = document.getElementById('page-content');

const deleteModalEl = document.getElementById('confirmDeleteModal');
const deleteModal   = new bootstrap.Modal(deleteModalEl);
let pendingDeleteId = null;

const params = new URLSearchParams(location.search);
const userId = parseInt(params.get('id'));

if (!userId || isNaN(userId)) {
  document.body.innerHTML = '<main class="admin-main"><p>Ungültige User-ID.</p></main>';
  throw new Error('Invalid user ID');
}

let user, allGroups, memberships;
try {
  const [userRes, groupsRes, membersRes] = await Promise.all([
    api.get('/ajax/users.php',         { id: userId }),
    api.get('/ajax/groups.php'),
    api.get('/ajax/group-members.php', { user_id: userId }),
  ]);

  if (!userRes.success) {
    document.body.innerHTML = '<main class="admin-main"><p>Nutzer nicht gefunden.</p></main>';
    throw new Error('User not found');
  }

  user        = userRes.data;
  allGroups   = groupsRes.data  ?? [];
  memberships = membersRes.data ?? [];
} catch (e) {
  document.body.innerHTML = '<main class="admin-main"><p>API nicht erreichbar.</p></main>';
  throw e;
}

renderPage();

function askDelete(userId) {
    pendingDeleteId = userId;
    deleteModal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
    deleteModal.hide();
    const res = await api.post('/ajax/users/delete.php', { id: pendingDeleteId });
    if (res.success) {
        window.location.href = 'adminpanel.php?msg=deleted';
    } else {
        showError(res.error ?? 'Löschen fehlgeschlagen.');
    }
});

function showSuccess(msg) {
  bannerSuccess.textContent = msg;
  bannerSuccess.classList.add('visible');
  bannerError.classList.remove('visible');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showError(msg) {
  bannerError.textContent = msg;
  bannerError.classList.add('visible');
  bannerSuccess.classList.remove('visible');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function clearMessages() {
  bannerSuccess.classList.remove('visible');
  bannerError.classList.remove('visible');
}

function renderPage() {
  const memberGroupIds = new Set(memberships.map(m => m.group_id));

  const groupOptions = allGroups
    .filter(g => !memberGroupIds.has(g.group_id))
    .map(g => `<option value="${parseInt(g.group_id)}">${escHtml(g.name)}</option>`)
    .join('');

  const memberRowsTable = memberships.map(m => {
    const group = allGroups.find(g => g.group_id === m.group_id);
    return `
      <tr>
        <td><span class="id-badge">${escHtml(String(m.group_id))}</span></td>
        <td>${escHtml(group?.name ?? '—')}</td>
        <td>
          <button type="button" class="btn btn-danger btn-sm remove-group"
            data-groupid="${parseInt(m.group_id)}">Entfernen</button>
        </td>
      </tr>`;
  }).join('');

  const memberCardsStack = memberships.map(m => {
    const group = allGroups.find(g => g.group_id === m.group_id);
    return `
      <div class="stack-card">
        <div class="stack-card__row">
          <span class="stack-card__label">ID</span>
          <span class="stack-card__value id-badge">${escHtml(String(m.group_id))}</span>
        </div>
        <div class="stack-card__row">
          <span class="stack-card__label">Name</span>
          <span class="stack-card__value">${escHtml(group?.name ?? '—')}</span>
        </div>
        <div class="stack-card__actions">
          <button type="button" class="btn btn-danger btn-sm remove-group"
            data-groupid="${parseInt(m.group_id)}">Entfernen</button>
        </div>
      </div>`;
  }).join('');

  pageContent.innerHTML = `
    <img id="easteregg" src="../assets/img/Comic.jpeg" alt="Profirallyefahrer Lars Pfitzenmeyer" style="display:none;">
    <div class="card">
      <div class="card__header"><span class="card__title">Profil</span></div>
      <div class="card__body">
        <form id="update-form">
          <div class="form-group">
            <label>User ID</label>
            <input type="text" value="${escHtml(String(user.user_id))}" disabled>
          </div>
          <div class="form-group">
            <label for="email">E-Mail Adresse</label>
            <input type="email" name="email" id="email" value="${escHtml(user.email)}" required>
          </div>
          <div class="form-group">
            <label for="password">Passwort <span class="muted" style="text-transform:none;font-weight:400;">(leer lassen = unverändert)</span></label>
            <input type="password" name="password" id="password" placeholder="Neues Passwort">
          </div>
          <button type="submit" class="btn btn-primary">Speichern</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card__header"><span class="card__title">Gruppen</span></div>
      <div class="card__body" style="padding:0;">
        <table class="admin-table">
          <thead>
            <tr><th>ID</th><th>Name</th><th>Action</th></tr>
          </thead>
          <tbody id="groups-tbody">
            ${memberRowsTable || '<tr><td colspan="3" class="muted" style="padding:1rem;">Keine Gruppenmitgliedschaften.</td></tr>'}
          </tbody>
        </table>
        <div id="groups-stack" class="card-stack" style="padding:1rem;">
          ${memberCardsStack || '<p class="muted">Keine Gruppenmitgliedschaften.</p>'}
        </div>
      </div>
      <div class="card__body" style="border-top:1px solid var(--border);">
        <form id="add-group-form">
          <div class="inline-add">
            <div class="form-group">
              <label>Gruppe hinzufügen</label>
              <select name="group_id" ${groupOptions ? '' : 'disabled'}>
                ${groupOptions || '<option>Alle Gruppen bereits zugewiesen</option>'}
              </select>
            </div>
            <button type="submit" class="btn btn-primary" ${groupOptions ? '' : 'disabled'}>
              Hinzufügen
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="card card--danger">
      <div class="card__header"><span class="card__title">Gefahrenbereich</span></div>
      <div class="card__body">
        <p style="color:var(--muted);margin-bottom:1rem;font-size:0.9rem;">
          Das Löschen eines Nutzers kann nicht rückgängig gemacht werden.
        </p>
        <form id="delete-form">
          <button type="submit" class="btn btn-danger">Nutzer unwiderruflich löschen</button>
        </form>
      </div>
    </div>
  `;


    if (user.email.toLowerCase() === "lars@pfizenmayer.de") {
        document.getElementById("easteregg").style.display = "block";
    }
  document.getElementById('update-form').addEventListener('submit', handleUpdate);
  document.getElementById('add-group-form').addEventListener('submit', handleAddGroup);
  document.getElementById('delete-form').addEventListener('submit', handleDelete);
  document.querySelectorAll('.remove-group').forEach(btn => {
    btn.addEventListener('click', () => handleRemoveGroup(parseInt(btn.dataset.groupid)));
  });
}

async function handleUpdate(e) {
  e.preventDefault();
  clearMessages();

  const email    = e.target.email.value.trim();
  const password = e.target.password.value;

  if (password) {
    const pwError = validatePassword(password);
    if (pwError) { showError(pwError); return; }
  }

  const payload = { id: userId, email };
  if (password) payload.password = password;

  try {
    const res = await api.post('/ajax/users/update.php', payload);
    if (res.success) {
      showSuccess('Profil erfolgreich aktualisiert.');
      e.target.password.value = '';
    } else {
      showError(res.error ?? 'Aktualisierung fehlgeschlagen.');
    }
  } catch (e) {
    showError('API-Kommunikationsfehler: ' + e.message);
  }
}

async function handleAddGroup(e) {
  e.preventDefault();
  clearMessages();
  const groupId = parseInt(e.target.group_id.value);
  try {
    const res = await api.post('/ajax/group-members/create.php', { user_id: userId, group_id: groupId });
    if (res.success) {
      memberships.push({ user_id: userId, group_id: groupId });
      renderPage();
      showSuccess('Gruppe hinzugefügt.');
    } else {
      showError(res.error ?? 'Hinzufügen fehlgeschlagen.');
    }
  } catch (e) {
    showError('API-Kommunikationsfehler: ' + e.message);
  }
}

async function handleRemoveGroup(groupId) {
  clearMessages();
  try {
    const res = await api.post('/ajax/group-members/delete.php', { user_id: userId, group_id: groupId });
    if (res.success) {
      memberships = memberships.filter(m => m.group_id !== groupId);
      renderPage();
      showSuccess('Gruppe entfernt.');
    } else {
      showError(res.error ?? 'Entfernen fehlgeschlagen.');
    }
  } catch (e) {
    showError('API-Kommunikationsfehler: ' + e.message);
  }
}

async function handleDelete(e) {
  e.preventDefault();
  askDelete(userId);
  clearMessages();
}

function escHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

</body>
</html>