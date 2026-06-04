<?php
require_once __DIR__ . '/../app/session/guard.php';
requireGuest();
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <?php include 'head.php'; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>

<body>
<nav class="navbar navbar-expand-lg px-3">
  <div class="container-fluid">
    <a class="navbar-brand" href="home.php">Pacenotes24.de</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="karte.php">Karte</a></li>
        <li class="nav-item"><a class="nav-link active" href="routen.php">Routen</a></li>
      </ul>
      <button class="btn btn-outline-danger btn-sm" onclick="logout()">Logout</button>
    </div>
  </div>
</nav>

<div class="modal-overlay" id="importModal" onclick="handleOverlayClick(event)">
  <div class="modal-box">
    <h2>Route <span>importieren</span></h2>
    <p>Lade eine eigene Pacenote-JSON hoch und öffne sie direkt im Viewer.</p>
    <div class="drop-zone" id="dropZone">
      <input type="file" id="fileInput" accept=".json" onchange="handleFileSelect(event)">
      <div class="drop-zone-icon">📂</div>
      <div class="drop-zone-text">Datei hierher ziehen oder <strong>klicken</strong></div>
      <div class="drop-zone-filename" id="selectedFileName"></div>
    </div>
    <div class="modal-actions">
      <button class="btn-modal-cancel" onclick="closeImport()">Abbrechen</button>
      <button class="btn-modal-open" id="btnOpenImport" disabled onclick="openImportedFile()">Im Viewer öffnen</button>
    </div>
  </div>
</div>

<div class="container-xxl">
  <div class="page-header">
    <div class="page-header-left">
      <h1>Alle <span>Routen</span></h1>
      <p id="routeSubtitle">Routen werden geladen…</p>
    </div>
    <div class="page-header-right">
      <button class="btn-import" onclick="openImport()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="17 8 12 3 7 8"/>
          <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
        JSON importieren
      </button>
    </div>
  </div>

  <div class="routes-grid" id="routesGrid"></div>
  <div class="load-more-row" id="loadMoreRow">
    <button class="btn-load-more" id="btnLoadMore" onclick="loadMoreRoutes()">
      Weitere Routen laden
    </button>
  </div>
</div>

<script>
/* ── API Config ─────────────────────────────────────────────────────────────
   Passe BASE_URL und ENDPOINT auf deine API an.
   Erwartet GET /api/routes?page=1&limit=9
   Response: { total: number, routes: [ { id, name, file, created_at, ... } ] }
   Alternativ: /api/routes liefert alle, dann lokal paginieren.
   ─────────────────────────────────────────────────────────────────────────── */
const API_BASE  = '/api';           // ← anpassen
const PER_PAGE  = 9;

let allRoutes    = [];   // vom Server geladene Metadaten
let visibleCount = 0;    // wie viele Karten gerade im DOM sind
let importedFile = null; // für Import-Modal

/* ── Utilities ──────────────────────────────────────────────────────────── */
function escHtml(str) {
  return String(str)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;');
}
function sevColor(s) {
  const map = {1:'#00d2d3',2:'#198754',3:'#ffc107',4:'#fd7e14',5:'#dc3545',6:'#9b0000'};
  return map[Math.min(Math.max(parseInt(s)||2,1),6)] || '#3b82f6';
}
function fmtDist(meters) {
  if (!meters) return null;
  return (meters / 1000).toFixed(2) + ' km';
}
function fmtDate(iso) {
  if (!iso) return null;
  try {
    return new Date(iso).toLocaleDateString('de-DE', { day:'2-digit', month:'2-digit', year:'numeric' });
  } catch { return null; }
}

/* ── localStorage comments ──────────────────────────────────────────────── */
function loadComments(id) {
  try { return JSON.parse(localStorage.getItem(`comments_route_${id}`)) || []; }
  catch { return []; }
}
function saveComments(id, arr) {
  localStorage.setItem(`comments_route_${id}`, JSON.stringify(arr));
}
function renderComments(id, listEl) {
  const comments = loadComments(id);
  listEl.innerHTML = '';
  if (!comments.length) {
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

/* ── Route JSON fetching ────────────────────────────────────────────────── */
const routeCache = {};
async function fetchRouteData(fileUrl) {
  if (routeCache[fileUrl]) return routeCache[fileUrl];
  const res = await fetch(fileUrl);
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  const data = await res.json();
  routeCache[fileUrl] = data;
  return data;
}

/* ── Canvas route preview ────────────────────────────────────────────────── */
function drawRoutePreview(canvas, notes) {
  if (!notes || notes.length < 2) return;

  const lats = notes.map(n => parseFloat(n.lat || n.latitude)).filter(v => !isNaN(v));
  const lngs = notes.map(n => parseFloat(n.lng || n.longitude || n.lon)).filter(v => !isNaN(v));
  if (lats.length < 2) return;

  const ctx = canvas.getContext('2d');
  const W = canvas.width, H = canvas.height;
  const PAD = 18;

  const minLat = Math.min(...lats), maxLat = Math.max(...lats);
  const minLng = Math.min(...lngs), maxLng = Math.max(...lngs);
  const rangeLat = maxLat - minLat || 1;
  const rangeLng = maxLng - minLng || 1;

  const toX = lng => PAD + ((lng - minLng) / rangeLng) * (W - PAD*2);
  const toY = lat => H - PAD - ((lat - minLat) / rangeLat) * (H - PAD*2);

  // Background subtle grid
  ctx.clearRect(0, 0, W, H);
  ctx.strokeStyle = 'rgba(255,255,255,.04)';
  ctx.lineWidth = 1;
  for (let i = 0; i <= 4; i++) {
    const x = PAD + (i/4)*(W-PAD*2);
    const y = PAD + (i/4)*(H-PAD*2);
    ctx.beginPath(); ctx.moveTo(x, PAD); ctx.lineTo(x, H-PAD); ctx.stroke();
    ctx.beginPath(); ctx.moveTo(PAD, y); ctx.lineTo(W-PAD, y); ctx.stroke();
  }

  // Route path with severity gradient
  for (let i = 0; i < notes.length - 1; i++) {
    const n = notes[i];
    const lat = parseFloat(n.lat || n.latitude);
    const lng = parseFloat(n.lng || n.longitude || n.lon);
    const nlat = parseFloat(notes[i+1].lat || notes[i+1].latitude);
    const nlng = parseFloat(notes[i+1].lng || notes[i+1].longitude || notes[i+1].lon);
    if (isNaN(lat)||isNaN(lng)||isNaN(nlat)||isNaN(nlng)) continue;
    ctx.strokeStyle = sevColor(n.severity);
    ctx.lineWidth = 2.2;
    ctx.lineCap = 'round';
    ctx.beginPath();
    ctx.moveTo(toX(lng), toY(lat));
    ctx.lineTo(toX(nlng), toY(nlat));
    ctx.stroke();
  }

  // Start / end markers
  const firstN = notes[0], lastN = notes[notes.length-1];
  const sx = toX(parseFloat(firstN.lng||firstN.longitude||firstN.lon));
  const sy = toY(parseFloat(firstN.lat||firstN.latitude));
  const ex = toX(parseFloat(lastN.lng||lastN.longitude||lastN.lon));
  const ey = toY(parseFloat(lastN.lat||lastN.latitude));

  if (!isNaN(sx) && !isNaN(sy)) {
    ctx.beginPath(); ctx.arc(sx, sy, 4, 0, Math.PI*2);
    ctx.fillStyle = '#00d2d3'; ctx.fill();
  }
  if (!isNaN(ex) && !isNaN(ey)) {
    ctx.beginPath(); ctx.arc(ex, ey, 4, 0, Math.PI*2);
    ctx.fillStyle = '#e8ff47'; ctx.fill();
  }
}

/* ── Severity dots row ───────────────────────────────────────────────────── */
function buildSevDots(notes) {
  const counts = {1:0,2:0,3:0,4:0,5:0,6:0};
  notes.forEach(n => { const s = Math.min(Math.max(parseInt(n.severity)||2,1),6); counts[s]++; });
  return Object.entries(counts).map(([s,c]) =>
    c > 0 ? `<div class="sev-dot s${s}" title="Severity ${s}: ${c}×"></div>` : `<div class="sev-dot"></div>`
  ).join('');
}

/* ── Build card DOM ─────────────────────────────────────────────────────── */
function buildCard(route) {
  const card = document.createElement('div');
  card.className = 'route-card';
  card.id = `card-${route.id}`;

  card.innerHTML = `
    <div class="card-top">
      <div class="card-title" id="title-${route.id}">
        ${escHtml(route.name || 'Route ' + String(route.id).padStart(2,'0'))}
      </div>
      <div class="card-dist" id="dist-${route.id}">—</div>
    </div>

    <div class="card-preview" id="preview-${route.id}">
      <div class="preview-loading">
        <div class="spinner-tiny"></div>
        <span>Lade Vorschau…</span>
      </div>
    </div>

    <div class="sev-row" id="sev-${route.id}"></div>

    <div class="card-bottom">
      <div class="card-created" id="created-${route.id}">
        ${route.created_at ? fmtDate(route.created_at) : '—'}
      </div>
      <button class="btn-open" id="btn-open-${route.id}" onclick="openInViewer('${escHtml(route.file)}')">
        Öffnen →
      </button>
    </div>

    <div class="comment-toggle-row">
      <button class="btn-comment-toggle" id="btn-ct-${route.id}" onclick="toggleComments('${route.id}')">
        <svg class="chevron" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
        <span id="comment-label-${route.id}">Kommentare (${loadComments(route.id).length})</span>
      </button>
    </div>

    <div class="comment-section" id="comments-${route.id}">
      <div class="comment-list" id="comment-list-${route.id}"></div>
      <div class="comment-form">
        <div class="comment-input-row">
          <input class="comment-name"     id="cname-${route.id}" type="text"  placeholder="Name" maxlength="30">
          <textarea class="comment-textarea" id="ctext-${route.id}" placeholder="Kommentar schreiben…" maxlength="300" rows="2"></textarea>
        </div>
        <button class="btn-submit-comment" onclick="submitComment('${route.id}')">Senden</button>
      </div>
    </div>`;

  return card;
}

/* Fill card with fetched route data */
async function hydrateCard(route) {
  try {
    const data = await fetchRouteData(route.file);
    const notes = data.notes || [];

    // Title
    const titleEl = document.getElementById(`title-${route.id}`);
    if (titleEl && (data.route?.name || route.name)) {
      titleEl.textContent = data.route?.name || route.name;
    }

    // Distance
    const distEl = document.getElementById(`dist-${route.id}`);
    if (distEl) {
      const d = fmtDist(data.route?.total_distance_m);
      distEl.textContent = d || '—';
    }

    // Created at (prefer API field, fallback to JSON)
    const createdEl = document.getElementById(`created-${route.id}`);
    if (createdEl && !route.created_at && data.route?.created_at) {
      createdEl.textContent = fmtDate(data.route.created_at) || '—';
    }

    // Severity dots
    const sevEl = document.getElementById(`sev-${route.id}`);
    if (sevEl && notes.length) sevEl.innerHTML = buildSevDots(notes);

    // Canvas preview
    const previewEl = document.getElementById(`preview-${route.id}`);
    if (previewEl && notes.length >= 2) {
      const canvas = document.createElement('canvas');
      canvas.width  = 320;
      canvas.height = 148;
      previewEl.innerHTML = '';
      previewEl.appendChild(canvas);
      drawRoutePreview(canvas, notes);
    } else if (previewEl) {
      previewEl.innerHTML = `<div class="preview-loading">
        <span>📍</span>
        <span>Keine Koordinaten</span>
      </div>`;
    }

  } catch (err) {
    const previewEl = document.getElementById(`preview-${route.id}`);
    if (previewEl) previewEl.innerHTML = `<div class="card-error">⚠ Nicht ladbar<br><code>${escHtml(route.file || '')}</code></div>`;
    const btnEl = document.getElementById(`btn-open-${route.id}`);
    if (btnEl) btnEl.disabled = true;
  }
}

/* ── API: load route list ────────────────────────────────────────────────── */
async function fetchRouteList() {
  const res = await fetch(`${API_BASE}/routes`);
  if (!res.ok) throw new Error(`API error ${res.status}`);
  const data = await res.json();
  // Supports { routes: [...] } or plain array
  return Array.isArray(data) ? data : (data.routes || []);
}

/* ── Render next batch ──────────────────────────────────────────────────── */
function renderBatch() {
  const grid = document.getElementById('routesGrid');
  const batch = allRoutes.slice(visibleCount, visibleCount + PER_PAGE);

  batch.forEach(route => {
    const card = buildCard(route);
    grid.appendChild(card);
    hydrateCard(route);
    visibleCount++;
  });

  // Update load-more button
  const btn = document.getElementById('btnLoadMore');
  if (visibleCount < allRoutes.length) {
    btn.textContent = `Weitere Routen laden (${allRoutes.length - visibleCount} verbleibend)`;
    btn.disabled = false;
  }
}

function loadMoreRoutes() {
  const btn = document.getElementById('btnLoadMore');
  btn.disabled = true;
  renderBatch();
}

/* ── Initialise ─────────────────────────────────────────────────────────── */
async function init() {
  const grid = document.getElementById('routesGrid');
  const subtitle = document.getElementById('routeSubtitle');

  // Show loading skeleton
  grid.innerHTML = `<div class="empty-state">
    <div>⏳</div>
    <div>Routen werden von der API geladen…</div>
  </div>`;

  try {
    allRoutes = await fetchRouteList();

    if (!allRoutes.length) {
      grid.innerHTML = `<div class="empty-state">
        <div>🗺️</div>
        <div>Keine Routen gefunden.</div>
      </div>`;
      subtitle.textContent = '0 Routen gefunden';
      return;
    }

    subtitle.textContent = `${allRoutes.length} Pacenote-Routen – JSON laden & kommentieren`;
    grid.innerHTML = '';
    renderBatch();

  } catch (err) {
    grid.innerHTML = `<div class="empty-state">
      <div>⚠️</div>
      <div>API nicht erreichbar.<br><code>${escHtml(err.message)}</code></div>
    </div>`;
    subtitle.textContent = 'Fehler beim Laden';
  }
}

/* ── Comments ────────────────────────────────────────────────────────────── */
function toggleComments(routeId) {
  const section = document.getElementById(`comments-${routeId}`);
  const btn     = document.getElementById(`btn-ct-${routeId}`);
  const list    = document.getElementById(`comment-list-${routeId}`);
  const isOpen  = section.classList.contains('open');
  section.classList.toggle('open', !isOpen);
  btn.classList.toggle('open', !isOpen);
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
    author, text,
    time: new Date().toLocaleString('de-DE', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' })
  });
  saveComments(routeId, comments);
  textEl.value = '';

  const label = document.getElementById('comment-label-' + routeId);
  if (label) label.textContent = `Kommentare (${comments.length})`;

  renderComments(routeId, document.getElementById(`comment-list-${routeId}`));
}

/* ── Viewer navigation ───────────────────────────────────────────────────── */
function openInViewer(fileUrl) {
  window.location.href = `index.php?route=${encodeURIComponent(fileUrl)}`;
}

/* ── Import modal ────────────────────────────────────────────────────────── */
function openImport() {
  document.getElementById('importModal').classList.add('open');
}
function closeImport() {
  document.getElementById('importModal').classList.remove('open');
  importedFile = null;
  document.getElementById('selectedFileName').textContent = '';
  document.getElementById('fileInput').value = '';
  document.getElementById('btnOpenImport').disabled = true;
}
function handleOverlayClick(e) {
  if (e.target === document.getElementById('importModal')) closeImport();
}

function handleFileSelect(e) {
  const file = e.target.files[0];
  if (!file) return;
  importedFile = file;
  document.getElementById('selectedFileName').textContent = '📄 ' + file.name;
  document.getElementById('btnOpenImport').disabled = false;
}

// Drag & drop
const dz = document.getElementById('dropZone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('drag-over'); });
dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
dz.addEventListener('drop', e => {
  e.preventDefault();
  dz.classList.remove('drag-over');
  const file = e.dataTransfer.files[0];
  if (file && file.name.endsWith('.json')) {
    importedFile = file;
    document.getElementById('selectedFileName').textContent = '📄 ' + file.name;
    document.getElementById('btnOpenImport').disabled = false;
  }
});

function openImportedFile() {
  if (!importedFile) return;
  const reader = new FileReader();
  reader.onload = e => {
    try {
      const data = JSON.parse(e.target.result);
      // Store in sessionStorage and open viewer with special flag
      sessionStorage.setItem('importedRoute', JSON.stringify(data));
      window.location.href = `index.php?route=__imported__`;
    } catch {
      alert('Ungültige JSON-Datei. Bitte überprüfe das Format.');
    }
  };
  reader.readAsText(importedFile);
}

/* ── Keyboard close modal ────────────────────────────────────────────────── */
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeImport();
});

/* ── Boot ────────────────────────────────────────────────────────────────── */
init();
</script>
</body>
</html>