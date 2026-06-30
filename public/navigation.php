
<?php
require_once __DIR__ . '/../app/session/guard.php';
requireAuth();
?>

 <!DOCTYPE html>
<html lang="de">
<head>
    <?php include './include/head.php'; ?>
</head>

<body>
<?php include './include/navbar.php'; ?>

<main class="container my-4">

      <!-- Lade-Controls -->
      <div class="card p-3 mb-3">
        <div class="row g-3 align-items-end">
          <div class="col-md-6">
            <label class="form-label small text-muted text-uppercase fw-bold">Eigene Route hochladen</label>
            <input type="file" id="fileInput" class="form-control" accept=".json">
          </div>
          <div class="col-md-6">
            <label class="form-label small text-muted text-uppercase fw-bold">Oder vom Server laden</label>
            <button type="button" class="btn btn-primary w-100" id="btnFetchRoutes">
              Routen vom Server abrufen
            </button>
            <select class="form-select mt-2 d-none" id="serverRoutesSelect">
              <option selected disabled>Wähle eine Server-Route...</option>
            </select>
          </div>
        </div>
      </div>

      <div class="row g-3">

        <!-- Karte -->
        <div class="col-lg-8">
          <div class="position-relative h-100">
            <div id="map" class="shadow d-flex align-items-center justify-content-center text-muted" style="min-height: 480px;">
              <span id="mapPlaceholder">JSON hochladen</span>
            </div>
            <button id="resetZoomBtn" class="btn btn-sm btn-secondary"
                    style="position:absolute; top:10px; right:10px; z-index:1000; display:none;">
              ⟳ Zoom zurücksetzen
            </button>
          </div>
        </div>

        <!--Kurve -->
        <div class="col-lg-4">
          <div class="card note-card-click p-4 shadow h-100" id="curveCard" style="display: none;">
            <div class="huge-arrow-container mb-3 shadow-sm">
              <svg id="turnArrow" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="10" stroke-linecap="round" stroke-linejoin="round">
                <path id="arrowPath" d="M 50 85 Q 50 50 50 20" />
                <polyline id="arrowHead" points="35,35 50,20 65,35" />
              </svg>
            </div>
            <div class="d-flex justify-content-between align-items-center" style="color: var(--text) !important;">
              <div class="note-display" id="noteName">--</div>
              <span class="badge text-uppercase badge-direction bg-primary" id="noteDirection">LEFT</span>
            </div>
          </div>
        </div>

        <!--Daten -->
        <div class="col-12">
          <div class="card p-4 shadow" id="dataCard" style="display: none;">
            <div class="row g-3 text-start" style="color: var(--text) !important;">
              <div class="col-6 col-md-3">
                <small class="text-muted d-block">Vom Start</small>
                <strong class="fs-5" id="distStart">0 m</strong>
              </div>
              <div class="col-6 col-md-3">
                <small class="text-muted d-block" id="distNextLabel">Bis nächste Kurve</small>
                <strong class="fs-5" id="distNext">0 m</strong>
              </div>
              <div class="col-6 col-md-3">
                <small class="text-muted d-block">Radius</small>
                <strong class="fs-5" id="noteRadius">0 m</strong>
              </div>
              <div class="col-6 col-md-3">
                <small class="text-muted d-block">Stärke (Severity)</small>
                <strong class="fs-5" id="noteSeverity">0</strong>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top" style="border-color: var(--border) !important;">
              <button id="prevNoteBtn" class="btn btn-secondary btn-sm">← Zurück</button>
              <span class="text-muted small text-uppercase tracking-wider" id="noteProgress">0 / 0</span>
              <button id="nextNoteBtn" class="btn btn-primary btn-sm">Weiter →</button>
            </div>
          </div>
        </div>

      </div>
    </main>
<?php include './include/footer.php'; ?>
        <script type="module" src="./assets/js/homepage.js"></script>
  </body>
</html>