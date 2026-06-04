<?php
$adminTitle      = 'Admin Panel';
$adminBreadcrumb = [['label' => 'User Management', 'href' => null]];
$adminToggle     = 'users';
require __DIR__ . '/_header.php';
?>

<main class="admin-main">

  <div id="banner-success" class="banner banner--success"></div>
  <div id="banner-error"   class="banner banner--error"></div>

  <div class="card">
    <div class="card__header">
      <span class="card__title">Alle Nutzer</span>
      <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
        <a href="create_user.php" class="btn btn-primary btn-sm">+ Nutzer anlegen</a>
      </div>
    </div>
    <div class="card__body" style="padding:0;">

      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>E-Mail</th>
            <th>Gruppen</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="user-tbody"></tbody>
      </table>

      <div id="user-stack" class="card-stack" style="padding:1rem;"></div>

    </div>
  </div>

</main>

<script type="module">
import { api } from '../assets/js/api.js';

const tbody       = document.getElementById('user-tbody');
const stack       = document.getElementById('user-stack');
const bannerError = document.getElementById('banner-error');

try {
  const [usersRes, groupsRes, membersRes] = await Promise.all([
    api.get('/ajax/users.php'),
    api.get('/ajax/groups.php'),
    api.get('/ajax/group-members.php'),
  ]);

  if (!usersRes.success || !Array.isArray(usersRes.data)) {
    throw new Error(usersRes.error ?? 'Unknown error');
  }

  usersRes.data.sort((a, b) => b.user_id - a.user_id);

  const groups  = groupsRes.data  ?? [];
  const members = membersRes.data ?? [];

  const groupMap  = Object.fromEntries(groups.map(g => [g.group_id, g.name]));
  const memberMap = {};
  for (const m of members) {
    if (!memberMap[m.user_id]) memberMap[m.user_id] = [];
    memberMap[m.user_id].push(groupMap[m.group_id] ?? `ID ${m.group_id}`);
  }

  if (usersRes.data.length === 0) {
    tbody.innerHTML = '<tr><td colspan="4" class="muted">Keine Nutzer vorhanden.</td></tr>';
  }

  for (const user of usersRes.data) {
    const groupNames = memberMap[user.user_id] ?? [];
    const tagsHtml   = groupNames.length
      ? groupNames.map(g => `<span class="tag">${escHtml(g)}</span>`).join('')
      : '<span class="muted">—</span>';
    const profileUrl = `user_detail.php?id=${parseInt(user.user_id)}`;

    // Table row
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><span class="id-badge">${escHtml(String(user.user_id))}</span></td>
      <td>${escHtml(user.email)}</td>
      <td><div class="tag-list">${tagsHtml}</div></td>
      <td><a href="${profileUrl}" class="btn btn-secondary btn-sm">Profil</a></td>
    `;
    tbody.appendChild(tr);

    // Stack card (mobile)
    const card = document.createElement('div');
    card.className = 'stack-card';
    card.innerHTML = `
      <div class="stack-card__row">
        <span class="stack-card__label">ID</span>
        <span class="stack-card__value id-badge">${escHtml(String(user.user_id))}</span>
      </div>
      <div class="stack-card__row">
        <span class="stack-card__label">E-Mail</span>
        <span class="stack-card__value">${escHtml(user.email)}</span>
      </div>
      <div class="stack-card__row">
        <span class="stack-card__label">Gruppen</span>
        <div class="tag-list" style="justify-content:flex-end;">${tagsHtml}</div>
      </div>
      <div class="stack-card__actions">
        <a href="${profileUrl}" class="btn btn-secondary btn-sm">Profil anzeigen</a>
      </div>
    `;
    stack.appendChild(card);
  }

  const msg = new URLSearchParams(location.search).get('msg');
  if (msg === 'deleted') {
    const b = document.getElementById('banner-success');
    b.textContent = 'Nutzer wurde erfolgreich gelöscht.';
    b.classList.add('visible');
    history.replaceState(null, '', location.pathname);
    setTimeout(() => b.classList.remove('visible'), 4000);
  }

} catch (e) {
  bannerError.textContent = 'Upstream service unavailable: ' + escHtml(e.message);
  bannerError.classList.add('visible');
}

function escHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

</body>
</html>