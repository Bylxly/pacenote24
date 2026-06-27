<?php
require_once __DIR__ . '/../app/session/guard.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <?php include 'head.php'; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>

<body>
<?php include 'navbar.php'; ?>

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

<script type="module">
import { api } from './assets/js/api.js';

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
      <div class="card-actions">
        <button class="btn-export" id="btn-export-${route.id}" onclick="exportRoute(${route.id})" title="Pacenotes als JSON exportieren">
          ⤓ JSON
        </button>
        <button class="btn-open" id="btn-open-${route.id}" onclick="openInViewer(${route.id})">
          Öffnen →
        </button>
      </div>
    </div>`;

  return card;
}

/* Fill card with data already loaded from the DB (pacenotes_data) */
async function hydrateCard(route) {
  const previewEl = document.getElementById(`preview-${route.id}`);
  try {
    let notes = [];
    if (route.pacenotes_data) {
      const data = typeof route.pacenotes_data === 'string'
        ? JSON.parse(route.pacenotes_data)
        : route.pacenotes_data;
      notes = data.notes || [];
    }

    // Distance (aus DB-Spalte distance_m)
    const distEl = document.getElementById(`dist-${route.id}`);
    if (distEl) distEl.textContent = fmtDist(route.distance_m) || '—';

    // Severity dots
    const sevEl = document.getElementById(`sev-${route.id}`);
    if (sevEl && notes.length) sevEl.innerHTML = buildSevDots(notes);

    // Canvas preview
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
        <span>Noch keine Pacenotes</span>
      </div>`;
    }
  } catch (err) {
    if (previewEl) previewEl.innerHTML = `<div class="card-error">⚠ Vorschau-Fehler</div>`;
  }
}

/* ── API: load route list ────────────────────────────────────────────────── */
async function fetchRouteList() {
  const data = await api.get('/ajax/routes.php');
  if (!data.success || !Array.isArray(data.data)) throw new Error(data.error || 'Ungültiges Format');
  // DB-Felder auf die von den Karten erwartete Form mappen
  return data.data.map(r => ({
    id:             r.route_id,
    name:           r.title,
    created_at:     r.compiled_time,
    distance_m:     r.distance_m,
    pacenotes_data: r.pacenotes_data,
  }));
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

    subtitle.textContent = `${allRoutes.length} Pacenote-Routen`;
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

/* ── JSON Export ─────────────────────────────────────────────────────────── */
function exportRoute(routeId) {
  const route = allRoutes.find(r => r.id === routeId);
  if (!route || !route.pacenotes_data) {
    alert('Für diese Route wurden noch keine Pacenotes generiert.');
    return;
  }
  // pacenotes_data kommt aus der DB als JSON-String (oder bereits als Objekt)
  const json = typeof route.pacenotes_data === 'string'
    ? route.pacenotes_data
    : JSON.stringify(route.pacenotes_data, null, 2);

  const blob = new Blob([json], { type: 'application/json' });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  const safeName = (route.name || `route-${routeId}`).replace(/[^a-z0-9_-]+/gi, '_');
  a.href = url;
  a.download = `${safeName}.json`;
  document.body.appendChild(a);
  a.click();
  a.remove();

  setTimeout(() => URL.revokeObjectURL(url), 60000);
}

/* ── Viewer navigation ───────────────────────────────────────────────────── */
function openInViewer(routeId) {
  window.location.href = `index.php?route_id=${encodeURIComponent(routeId)}`;
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
      // Pacenote-JSON im Viewer (navigation.php) öffnen
      sessionStorage.setItem('importedRoute', JSON.stringify(data));
      window.location.href = `navigation.php`;
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

/* ── onclick-Handler global verfügbar machen (nötig im Modul-Scope) ───────── */
Object.assign(window, {
  openImport, closeImport, handleOverlayClick, handleFileSelect,
  openImportedFile, exportRoute, openInViewer, loadMoreRoutes,
});

/* ── Boot ────────────────────────────────────────────────────────────────── */
init();
</script>
</body>
</html>