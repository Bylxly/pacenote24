<?php
$adminTitle      = 'Route Detail';
$adminBreadcrumb = [
  ['label' => 'Pacenotes',       'href' => 'pacenote_view.php'],
  ['label' => 'Route Detail',    'href' => null],
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

const bannerSuccess = document.getElementById('banner-success');
const bannerError   = document.getElementById('banner-error');
const pageContent   = document.getElementById('page-content');

const deleteModalEl = document.getElementById('confirmDeleteModal');
const deleteModal   = new bootstrap.Modal(deleteModalEl);
let pendingDeleteId = null;

const params  = new URLSearchParams(location.search);
const routeId = parseInt(params.get('id'));

if (!routeId || isNaN(routeId)) {
  document.body.innerHTML = '<main class="admin-main"><p>Ungültige Route-ID.</p></main>';
  throw new Error('Invalid route ID');
}

let route, allUsers, allGroups, visibleUsers, visibleGroups;

try {
  const [routeRes, usersRes, groupsRes, visUsersRes, visGroupsRes] = await Promise.all([
    api.get('/ajax/routes.php',               { id: routeId }),
    api.get('/ajax/users.php'),
    api.get('/ajax/groups.php'),
    api.get('/ajax/track-visible-users.php',  { route_id: routeId }),
    api.get('/ajax/track-visible-groups.php', { route_id: routeId }),
  ]);

  if (!routeRes.success) {
    document.body.innerHTML = '<main class="admin-main"><p>Route nicht gefunden.</p></main>';
    throw new Error('Route not found');
  }

  route         = routeRes.data;
  allUsers      = usersRes.data   ?? [];
  allGroups     = groupsRes.data  ?? [];
  visibleUsers  = visUsersRes.data  ?? [];
  visibleGroups = visGroupsRes.data ?? [];
} catch (e) {
  document.body.innerHTML = '<main class="admin-main"><p>API nicht erreichbar.</p></main>';
  throw e;
}

renderPage();

function askDelete(routeId) {
    pendingDeleteId = routeId;
    deleteModal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
    deleteModal.hide();
    const res = await api.post('/ajax/routes/delete.php', { id: pendingDeleteId });
    if (res.success) {
        window.location.href = 'pacenote_view.php?msg=deleted';
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

function buildVisibilitySection({ sectionTitle, items, allItems, idKey, nameKey, addEndpoint, deleteEndpoint, addPayloadKey, deletePayloadKey }) {
  const ids = new Set(items.map(i => i[idKey]));

  const options = allItems
    .filter(i => !ids.has(i[idKey]))
    .map(i => `<option value="${parseInt(i[idKey])}">${escHtml(i[nameKey])}</option>`)
    .join('');

  const tableRows = items.map(item => {
    const match = allItems.find(i => i[idKey] === item[idKey]);
    return `
      <tr>
        <td><span class="id-badge">${escHtml(String(item[idKey]))}</span></td>
        <td>${escHtml(match?.[nameKey] ?? '—')}</td>
        <td>
          <button type="button" class="btn btn-danger btn-sm remove-vis"
            data-id="${parseInt(item[idKey])}" data-endpoint="${deleteEndpoint}" data-key="${deletePayloadKey}">
            Entfernen
          </button>
        </td>
      </tr>`;
  }).join('');

  const stackCards = items.map(item => {
    const match = allItems.find(i => i[idKey] === item[idKey]);
    return `
      <div class="stack-card">
        <div class="stack-card__row">
          <span class="stack-card__label">ID</span>
          <span class="stack-card__value id-badge">${escHtml(String(item[idKey]))}</span>
        </div>
        <div class="stack-card__row">
          <span class="stack-card__label">Name</span>
          <span class="stack-card__value">${escHtml(match?.[nameKey] ?? '—')}</span>
        </div>
        <div class="stack-card__actions">
          <button type="button" class="btn btn-danger btn-sm remove-vis"
            data-id="${parseInt(item[idKey])}" data-endpoint="${deleteEndpoint}" data-key="${deletePayloadKey}">
            Entfernen
          </button>
        </div>
      </div>`;
  }).join('');

  return `
    <div class="card">
      <div class="card__header"><span class="card__title">${escHtml(sectionTitle)}</span></div>
      <div class="card__body" style="padding:0;">
        <table class="admin-table">
          <thead><tr><th>ID</th><th>Name</th><th>Action</th></tr></thead>
          <tbody>
            ${tableRows || '<tr><td colspan="3" class="muted" style="padding:1rem;">Keine Einträge.</td></tr>'}
          </tbody>
        </table>
        <div class="card-stack" style="padding:1rem;">
          ${stackCards || '<p class="muted">Keine Einträge.</p>'}
        </div>
      </div>
      <div class="card__body" style="border-top:1px solid var(--border);">
        <form class="add-vis-form" data-endpoint="${addEndpoint}" data-key="${addPayloadKey}" data-idkey="${idKey}">
          <div class="inline-add">
            <div class="form-group">
              <label>Hinzufügen</label>
              <select name="entry_id" ${options ? '' : 'disabled'}>
                ${options || '<option>Alle bereits hinzugefügt</option>'}
              </select>
            </div>
            <button type="submit" class="btn btn-primary" ${options ? '' : 'disabled'}>
              Hinzufügen
            </button>
          </div>
        </form>
      </div>
    </div>`;
}

function renderPage() {
  pageContent.innerHTML = `
    <div class="card">
      <div class="card__header"><span class="card__title">Route</span></div>
      <div class="card__body">
        <form id="update-form">
          <div class="form-group">
            <label>Route ID</label>
            <input type="text" value="${escHtml(String(route.route_id))}" disabled>
          </div>
          <div class="form-group">
            <label>Owner (User ID)</label>
            <input type="text" value="${escHtml(String(route.owner_user_id))}" disabled>
          </div>
          <div class="form-group">
            <label>Zuletzt kompiliert</label>
            <input type="text" value="${escHtml(route.compiled_time ?? '—')}" disabled>
          </div>
          <div class="form-group">
            <label for="title">Titel</label>
            <input type="text" name="title" id="title" value="${escHtml(route.title ?? '')}" required>
          </div>
          <button type="submit" class="btn btn-primary">Speichern</button>
        </form>
      </div>
    </div>

    ${buildVisibilitySection({
      sectionTitle:   'Sichtbarkeit – Benutzer',
      items:          visibleUsers,
      allItems:       allUsers,
      idKey:          'user_id',
      nameKey:        'email',
      addEndpoint:    '/ajax/track-visible-users/create.php',
      deleteEndpoint: '/ajax/track-visible-users/delete.php',
      addPayloadKey:  'user_id',
      deletePayloadKey: 'user_id',
    })}

    ${buildVisibilitySection({
      sectionTitle:   'Sichtbarkeit – Gruppen',
      items:          visibleGroups,
      allItems:       allGroups,
      idKey:          'group_id',
      nameKey:        'name',
      addEndpoint:    '/ajax/track-visible-groups/create.php',
      deleteEndpoint: '/ajax/track-visible-groups/delete.php',
      addPayloadKey:  'group_id',
      deletePayloadKey: 'group_id',
    })}

    <div class="card card--danger">
      <div class="card__header"><span class="card__title">Gefahrenbereich</span></div>
      <div class="card__body">
        <p style="color:var(--muted);margin-bottom:1rem;font-size:0.9rem;">
          Das Löschen einer Route kann nicht rückgängig gemacht werden.
        </p>
        <form id="delete-form">
          <button type="submit" class="btn btn-danger">Route unwiderruflich löschen</button>
        </form>
      </div>
    </div>
  `;

  document.getElementById('update-form').addEventListener('submit', handleUpdate);
  document.getElementById('delete-form').addEventListener('submit', handleDelete);

  document.querySelectorAll('.add-vis-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      clearMessages();
      const entryId  = parseInt(form.entry_id.value);
      const endpoint = form.dataset.endpoint;
      const payKey   = form.dataset.key;
      const idKey    = form.dataset.idkey;
      const payload  = { route_id: routeId, [payKey]: entryId };
      try {
        const res = await api.post(endpoint, payload);
        if (res.success) {
          if (idKey === 'user_id') visibleUsers.push({ user_id: entryId, route_id: routeId });
          else                     visibleGroups.push({ group_id: entryId, route_id: routeId });
          renderPage();
          showSuccess('Eintrag hinzugefügt.');
        } else {
          showError(res.error ?? 'Hinzufügen fehlgeschlagen.');
        }
      } catch (err) {
        showError('API-Kommunikationsfehler: ' + err.message);
      }
    });
  });

  document.querySelectorAll('.remove-vis').forEach(btn => {
    btn.addEventListener('click', async () => {
      clearMessages();
      const entryId  = parseInt(btn.dataset.id);
      const endpoint = btn.dataset.endpoint;
      const payKey   = btn.dataset.key;
      const payload  = { route_id: routeId, [payKey]: entryId };
      try {
        const res = await api.post(endpoint, payload);
        if (res.success) {
          if (payKey === 'user_id') visibleUsers  = visibleUsers.filter(v => v.user_id  !== entryId);
          else                      visibleGroups = visibleGroups.filter(v => v.group_id !== entryId);
          renderPage();
          showSuccess('Eintrag entfernt.');
        } else {
          showError(res.error ?? 'Entfernen fehlgeschlagen.');
        }
      } catch (err) {
        showError('API-Kommunikationsfehler: ' + err.message);
      }
    });
  });
}

async function handleUpdate(e) {
  e.preventDefault();
  clearMessages();
  const title = e.target.title.value.trim();
  try {
    const res = await api.post('/ajax/routes/update.php', {
      id:        routeId,
      title,
      json_data: JSON.parse(route.json_data ?? '{}'),
    });
    if (res.success) {
      route.title = title;
      showSuccess('Route erfolgreich aktualisiert.');
    } else {
      showError(res.error ?? 'Aktualisierung fehlgeschlagen.');
    }
  } catch (e) {
    showError('API-Kommunikationsfehler: ' + e.message);
  }
}

async function handleDelete(e) {
  e.preventDefault();
  askDelete(routeId);
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