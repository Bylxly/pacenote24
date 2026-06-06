<?php
require_once __DIR__ . '/../app/session/guard.php'; 
requireAuth();
?>
<html lang="de">
<head>
    <?php include 'head.php'; ?>
</head>

<body>
<!--Navbar -->
<nav class="navbar navbar-expand-lg px-3">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Pacenotes24.de</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="landing_page.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php">Karte</a></li>
        <li class="nav-item"><a class="nav-link active" href="routen.php">Routen</a></li>
      </ul>
        <button class="btn btn-outline-danger btn-sm" onclick="logout()">Logout</button>
    </div>
  </div>
</nav>

<div class="container-xxl">
  <div class="page-header">
    <h1>Alle <span>Routen</span></h1>
    <p id="routeCount">Wird geladen…</p>
  </div>

  <div class="routes-grid" id="routesGrid"></div>
</div>

<script>
function sevColor(s) {
  const map = { 1:'#00d2d3', 2:'#198754', 3:'#ffc107', 4:'#fd7e14', 5:'#dc3545', 6:'#9b0000' };
  return map[Math.min(Math.max(parseInt(s)||2, 1), 6)] || '#3b82f6';
}

function loadComments(routeId) {
  try { return JSON.parse(localStorage.getItem(`comments_route_${routeId}`)) || []; }
  catch { return []; }
}
function saveComments(routeId, arr) {
  localStorage.setItem(`comments_route_${routeId}`, JSON.stringify(arr));
}

function renderComments(routeId, listEl) {
  const comments = loadComments(routeId);
  listEl.innerHTML = '';
  if (comments.length === 0) {
    listEl.innerHTML = '<p class="no-comments">Noch keine Kommentare.</p>';
    return;
  }
  comments.forEach(c => {
    const item = document.createElement('div');
    item.className = 'comment-item';
    item.innerHTML = `
      <div class="comment-meta">
        <span class="comment-author">${escHtml(c.author)}</span>
        <span class="comment-time">${c.time}</span>
      </div>
      <div class="comment-text">${escHtml(c.text)}</div>`;
    listEl.appendChild(item);
  });
  listEl.scrollTop = listEl.scrollHeight;
}

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function buildSevBar(notes) {
  const counts = {1:0,2:0,3:0,4:0,5:0,6:0};
  notes.forEach(n => { const s = Math.min(Math.max(parseInt(n.severity)||2,1),6); counts[s]++; });
  return Object.entries(counts).map(([s, c]) =>
    c > 0 ? `<div class="sev-dot s${s}" title="Severity ${s}: ${c}×"></div>` : `<div class="sev-dot"></div>`
  ).join('');
}

function buildNoteChips(notes) {
  const max = 8;
  const chips = notes.slice(0, max).map(n => {
    const color = sevColor(n.severity);
    return `<span class="note-chip" style="color:${color};border-color:${color}22;background:${color}11">${escHtml(n.note||'?')}</span>`;
  });
  if (notes.length > max) chips.push(`<span class="note-chip" style="color:var(--muted);border-color:var(--border)">+${notes.length - max}</span>`);
  return chips.join('');
}

function buildCard(route) {
  const card = document.createElement('div');
  card.className = 'route-card';
  card.id = `card-${route.id}`;
  card.innerHTML = `
    <div class="card-stripe"></div>
    <div class="card-body-inner">
      <div class="card-slot">Route ${String(route.id).padStart(2,'0')}</div>
      <div class="card-title" id="title-${route.id}">${route.label}</div>
      <div id="meta-${route.id}">
        <div class="card-loading">
          <div class="spinner-tiny"></div>
          <span>Lade JSON…</span>
        </div>
      </div>
    </div>
    <div class="card-actions">
      <button class="btn-load" id="btn-load-${route.id}" onclick="openInViewer(${route.id})">
        In Viewer öffnen
      </button>
      <button class="btn-comment-toggle" id="btn-ct-${route.id}" onclick="toggleComments(${route.id})">
        Kommentare
      </button>
    </div>
    <div class="comment-section" id="comments-${route.id}">
      <h6>Kommentare</h6>
      <div class="comment-list" id="comment-list-${route.id}"></div>
      <div class="comment-form">
        <div class="comment-input-row">
          <input  class="comment-name"     id="cname-${route.id}"  type="text"      placeholder="Name" maxlength="30" />
          <textarea class="comment-textarea" id="ctext-${route.id}" placeholder="Kommentar schreiben…" maxlength="300" rows="2"></textarea>
        </div>
        <button class="btn-submit-comment" onclick="submitComment(${route.id})">Senden</button>
      </div>
    </div>`;
  return card;
}

function fillMeta(routeId, data) {
  const metaEl = document.getElementById(`meta-${routeId}`);
  const titleEl = document.getElementById(`title-${routeId}`);
  const notes = data.notes || [];
  const dist = data.route?.total_distance_m ? `${(data.route.total_distance_m / 1000).toFixed(2)} km` : '—';
  const noteCount = data.route?.total_notes ?? notes.length;

  const route = ROUTES.find(r => r.id === routeId);
  titleEl.textContent = data.route?.name || route?.label || `Route ${routeId}`;

  metaEl.innerHTML = `
    <div class="meta-row">
      <span class="meta-pill">📍 <strong>${dist}</strong></span>
      <span class="meta-pill">🔢 <strong>${noteCount}</strong> Notes</span>
    </div>
    <div class="sev-bar">${buildSevBar(notes)}</div>
    <div class="notes-preview">${buildNoteChips(notes)}</div>`;
}
let ROUTES = [];
const routeCache = {};

async function fetchRoute(routeId) {
  if (routeCache[routeId]) return routeCache[routeId];
  const route = ROUTES.find(r => r.id === routeId);
  const res = await fetch(route.file);
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  const data = await res.json();
  routeCache[routeId] = data;
  return data;
}

function toggleComments(routeId) {
  const section = document.getElementById(`comments-${routeId}`);
  const btn     = document.getElementById(`btn-ct-${routeId}`);
  const list    = document.getElementById(`comment-list-${routeId}`);
  const isOpen  = section.classList.contains('open');
  section.classList.toggle('open', !isOpen);
  btn.classList.toggle('active', !isOpen);
  if (!isOpen) renderComments(routeId, list);
}

function submitComment(routeId) {
  const nameEl = document.getElementById(`cname-${routeId}`);
  const textEl = document.getElementById(`ctext-${routeId}`);
  const author = nameEl.value.trim() || 'Anonym';
  const text   = textEl.value.trim();
  if (!text) { textEl.focus(); return; }

  const comments = loadComments(routeId);
  comments.push({
    author,
    text,
    time: new Date().toLocaleString('de-DE', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' })
  });
  saveComments(routeId, comments);
  textEl.value = '';
  renderComments(routeId, document.getElementById(`comment-list-${routeId}`));
}
function openInViewer(routeId) {
  window.location.href = `index.php?route_id=${routeId}`;
}

const grid = document.getElementById('routesGrid');

async function loadRoutes() {
  const countEl = document.getElementById('routeCount');
  try {
    const resp = await fetch('/ajax/routes.php');
    const result = await resp.json();
    if (!result.success) throw new Error(result.error);

    ROUTES = result.data.map((r, i) => ({
      id:    r.route_id,
      label: r.title || `Route ${String(i + 1).padStart(2, '0')}`,
    }));

    result.data.forEach(r => {
      const jsonData = typeof r.json_data === 'string' ? JSON.parse(r.json_data) : r.json_data;
      routeCache[r.route_id] = {
        ...jsonData,
        route: { ...(jsonData.route || {}), total_distance_m: r.distance_m ?? null },
      };
    });

    countEl.textContent = `${ROUTES.length} Pacenote-Dateien – JSON laden & kommentieren`;

    ROUTES.forEach(route => {
      const card = buildCard(route);
      grid.appendChild(card);

      fetchRoute(route.id)
        .then(data => fillMeta(route.id, data))
        .catch(err => {
          const metaEl = document.getElementById(`meta-${route.id}`);
          metaEl.innerHTML = `<div class="card-error">⚠ ${escHtml(err.message)}</div>`;
          document.getElementById(`btn-load-${route.id}`).disabled = true;
        });
    });
  } catch (err) {
    document.getElementById('routeCount').textContent = '';
    grid.innerHTML = `<div class="card-error">⚠ ${escHtml(err.message)}</div>`;
  }
}

loadRoutes();
</script>
</body>
</html>
