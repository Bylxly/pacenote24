 
<?php
require_once __DIR__ . '/../app/session/guard.php';
requireAuth();
?>
 
 <!DOCTYPE html>
<html lang="de">
  <head>
    <?php include 'head.php'; ?>
  </head>

  <body>
        <!-- Navbar-->
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
            <li class="nav-item"><a class="nav-link" href="routen.php">Routen</a></li>
          </ul>
            <button class="btn btn-outline-danger btn-sm" onclick="logout()">Logout</button>        </div>
      </div>
    </nav>

<main class="container my-5">
      <div class="row g-4">
        
        <div class="col-lg-5">
          <div class="card p-4 mb-4">
            <h5 class="card-title mb-3">Eigene Route hochladen</h5>
            <input type="file" id="fileInput" class="form-control" accept=".json">
          </div>

          <div class="card note-card-click p-4 shadow" id="noteCard" style="display: none;">
            
            <div class="huge-arrow-container mb-4 shadow-sm">
              <svg id="turnArrow" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="10" stroke-linecap="round" stroke-linejoin="round">
                <path id="arrowPath" d="M 50 85 Q 50 50 50 20" />
                <polyline id="arrowHead" points="35,35 50,20 65,35" />
              </svg>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3" style="border-color: var(--border) !important;">
              <div class="note-display" id="noteName">--</div>
              <span class="badge text-uppercase badge-direction bg-primary" id="noteDirection">LEFT</span>
            </div>

            <div class="row g-3 text-start">
              <div class="col-6">
                <small class="text-muted d-block">Vom Start</small>
                <strong class="fs-5" id="distStart">0 m</strong>
              </div>
              <div class="col-6">
                <small class="text-muted d-block" id="distNextLabel">Bis nächste Kurve</small>
                <strong class="fs-5" id="distNext">0 m</strong>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">Radius</small>
                <strong class="fs-5" id="noteRadius">0 m</strong>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">Stärke (Severity)</small>
                <strong class="fs-5" id="noteSeverity">0</strong>
              </div>
            </div>

            <div class="mt-4 pt-3 border-top text-center text-muted small text-uppercase tracking-wider" id="noteProgress" style="border-color: var(--border) !important;">
              Klicken für nächste Note (0 / 0)
            </div>
          </div>
        </div>

        <div class="col-lg-7">
          <div id="map" class="shadow d-flex align-items-center justify-content-center text-muted">
            <span id="mapPlaceholder">JSON hochladen</span>
          </div>
        </div>

      </div>
    </main>
  </body>
</html>