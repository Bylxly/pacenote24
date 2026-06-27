<?php
$adminTitle      = 'Gruppenverwaltung';
$adminBreadcrumb = [['label' => 'Group Management', 'href' => null]];
$adminToggle     = 'groups';
require __DIR__ . '/_header.php';
?>

<main class="admin-main">

  <div id="banner-success" class="banner banner--success"></div>
  <div id="banner-error"   class="banner banner--error"></div>

  <div class="card">
    <div class="card__header">
      <span class="card__title">Alle Gruppen</span>
    </div>

    <div class="card__body" style="border-bottom:1px solid var(--border);">
      <form id="create-form">
        <div class="inline-add">
          <div class="form-group" style="flex:1;">
            <label for="new-group-name">Neue Gruppe</label>
            <input type="text" id="new-group-name" name="name" maxlength="50" placeholder="Gruppenname" required>
          </div>
          <button type="submit" class="btn btn-primary">Anlegen</button>
        </div>
      </form>
    </div>

    <div class="card__body" style="padding:0;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Mitglieder</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="group-tbody"></tbody>
      </table>
      <div id="group-stack" class="card-stack" style="padding:1rem;"></div>
    </div>
  </div>

</main>

<script type="module">
import { api } from '../assets/js/api.js';

const ADMIN_GROUP_ID = 1; // "Admins" darf nicht gelöscht werden

const tbody         = document.getElementById('group-tbody');
const stack         = document.getElementById('group-stack');
const bannerSuccess = document.getElementById('banner-success');
const bannerError   = document.getElementById('banner-error');
const createForm    = document.getElementById('create-form');

// Lösch-Modal aus _header.php
const deleteModalEl = document.getElementById('confirmDeleteModal');
const deleteModal   = new bootstrap.Modal(deleteModalEl);
let pendingDeleteId = null;

let groups   = [];
let counts   = {};   // group_id -> Mitgliederzahl
let editingId = null;

init();

async function init() {
  try {
    const [groupsRes, membersRes] = await Promise.all([
      api.get('/ajax/groups.php'),
      api.get('/ajax/group-members.php'),
    ]);
    if (!groupsRes.success || !Array.isArray(groupsRes.data)) {
      throw new Error(groupsRes.error ?? 'Unbekannter Fehler');
    }
    groups = groupsRes.data.sort((a, b) => a.group_id - b.group_id);
    counts = {};
    for (const m of (membersRes.data ?? [])) {
      counts[m.group_id] = (counts[m.group_id] ?? 0) + 1;
    }
    render();
  } catch (e) {
    showError('Daten konnten nicht geladen werden: ' + e.message);
  }
}

function render() {
  if (groups.length === 0) {
    tbody.innerHTML = '<tr><td colspan="4" class="muted" style="padding:1rem;">Keine Gruppen vorhanden.</td></tr>';
    stack.innerHTML = '<p class="muted">Keine Gruppen vorhanden.</p>';
    return;
  }

  tbody.innerHTML = '';
  stack.innerHTML = '';

  for (const g of groups) {
    const isAdmin   = g.group_id === ADMIN_GROUP_ID;
    const memberCnt = counts[g.group_id] ?? 0;
    const editing   = editingId === g.group_id;

    // ── Tabellenzeile
    const tr = document.createElement('tr');
    const nameCell = editing
      ? `<input type="text" class="edit-name" value="${escHtml(g.name)}" maxlength="50" style="max-width:220px;">`
      : escHtml(g.name);

    const actionCell = editing
      ? `<button class="btn btn-primary btn-sm save-edit"   data-id="${g.group_id}">Speichern</button>
         <button class="btn btn-secondary btn-sm cancel-edit">Abbrechen</button>`
      : `<button class="btn btn-secondary btn-sm rename" data-id="${g.group_id}">Umbenennen</button>
         <button class="btn btn-danger btn-sm delete" data-id="${g.group_id}" data-name="${escHtml(g.name)}" ${isAdmin ? 'disabled title="Admin-Gruppe kann nicht gelöscht werden"' : ''}>Löschen</button>`;

    tr.innerHTML = `
      <td><span class="id-badge">${g.group_id}</span></td>
      <td>${nameCell}</td>
      <td>${memberCnt}</td>
      <td>${actionCell}</td>`;
    tbody.appendChild(tr);

    // ── Stack-Karte (mobil)
    const card = document.createElement('div');
    card.className = 'stack-card';
    card.innerHTML = `
      <div class="stack-card__row">
        <span class="stack-card__label">ID</span>
        <span class="stack-card__value id-badge">${g.group_id}</span>
      </div>
      <div class="stack-card__row">
        <span class="stack-card__label">Name</span>
        <span class="stack-card__value">${nameCell}</span>
      </div>
      <div class="stack-card__row">
        <span class="stack-card__label">Mitglieder</span>
        <span class="stack-card__value">${memberCnt}</span>
      </div>
      <div class="stack-card__actions">${actionCell}</div>`;
    stack.appendChild(card);
  }

  wireRowButtons();
}

function wireRowButtons() {
  document.querySelectorAll('.rename').forEach(b =>
    b.addEventListener('click', () => { editingId = parseInt(b.dataset.id); render(); }));
  document.querySelectorAll('.cancel-edit').forEach(b =>
    b.addEventListener('click', () => { editingId = null; render(); }));
  document.querySelectorAll('.save-edit').forEach(b =>
    b.addEventListener('click', () => saveRename(parseInt(b.dataset.id), b)));
  document.querySelectorAll('.delete').forEach(b =>
    b.addEventListener('click', () => askDelete(parseInt(b.dataset.id), b.dataset.name)));
}

async function saveRename(groupId, btn) {
  const input = btn.closest('tr, .stack-card').querySelector('.edit-name');
  const name  = input.value.trim();
  if (!name) { showError('Name darf nicht leer sein.'); return; }
  try {
    const res = await api.post('/ajax/groups/update.php', { id: groupId, name });
    if (res.success) {
      const g = groups.find(x => x.group_id === groupId);
      if (g) g.name = name;
      editingId = null;
      render();
      showSuccess('Gruppe umbenannt.');
    } else {
      showError(res.error ?? 'Umbenennen fehlgeschlagen.');
    }
  } catch (e) {
    showError('API-Fehler: ' + e.message);
  }
}

function askDelete(groupId, name) {
  pendingDeleteId = groupId;
  deleteModalEl.querySelector('.modal-body').textContent =
    `Gruppe "${name}" wirklich löschen? Mitgliedschaften und Routen-Freigaben dieser Gruppe gehen verloren.`;
  deleteModal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
  deleteModal.hide();
  try {
    const res = await api.post('/ajax/groups/delete.php', { id: pendingDeleteId });
    if (res.success) {
      groups = groups.filter(g => g.group_id !== pendingDeleteId);
      render();
      showSuccess('Gruppe gelöscht.');
    } else {
      showError(res.error ?? 'Löschen fehlgeschlagen.');
    }
  } catch (e) {
    showError('API-Fehler: ' + e.message);
  }
});

createForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const nameInput = document.getElementById('new-group-name');
  const name = nameInput.value.trim();
  if (!name) { showError('Name darf nicht leer sein.'); return; }
  try {
    const res = await api.post('/ajax/groups/create.php', { name });
    if (res.success) {
      groups.push({ group_id: res.group_id, name });
      groups.sort((a, b) => a.group_id - b.group_id);
      createForm.reset();
      render();
      showSuccess(`Gruppe "${name}" angelegt.`);
    } else {
      showError(res.error ?? 'Anlegen fehlgeschlagen.');
    }
  } catch (e) {
    showError('API-Fehler: ' + e.message);
  }
});

function showSuccess(msg) {
  bannerSuccess.textContent = msg;
  bannerSuccess.classList.add('visible');
  bannerError.classList.remove('visible');
  window.scrollTo({ top: 0, behavior: 'smooth' });
  setTimeout(() => bannerSuccess.classList.remove('visible'), 4000);
}

function showError(msg) {
  bannerError.textContent = msg;
  bannerError.classList.add('visible');
  bannerSuccess.classList.remove('visible');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function escHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

</body>
</html>
