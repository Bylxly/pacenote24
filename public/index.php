<?php
require_once __DIR__ . '/../app/session/guard.php'; 
requireAuth();
?>
<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pacenotes24.de</title>
    <meta name="uid" content="<?= (int)$_SESSION['account_id'] ?>" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Custom CSS-->
    <link href="./assets/css/stylesheetmain.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  </head>

  <body>
  <nav class="navbar navbar-expand-lg px-3">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">Pacenotes24.de</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link active" href="index.php">Karte</a></li>
          <li class="nav-item"><a class="nav-link" href="routen.php">Routen</a></li>
          <li class="nav-item"><a class="nav-link" href="leaderboard.php">Leaderboard</a></li>
        </ul>
          <button class="btn btn-outline-danger btn-sm" onclick="logout()">Logout</button>
      </div>
    </div>
  </nav>

  <div class="container py-4">
    <div class="row g-4"> <div class="col-lg-4">
        <div class="card h-100"> <div class="card-body">
            <h5 class="mb-4"></h5>
            <div class="mb-3">
              <label for="startOrt" class="form-label">Startpunkt</label>
              <input type="text" class="form-control" id="startOrt" placeholder="z. B. DHBW Mannheim" />
            </div>
            <div class="mb-4">
              <label for="zielOrt" class="form-label">Zielpunkt</label>
              <input type="text" class="form-control" id="zielOrt" placeholder="z. B. Mannheim, Kaiserring 10" />
            </div>
            <button id="routeBtn" class="btn btn-primary w-100">Route berechnen</button>
            <button id="saveBtn" class="btn btn-success w-100 mt-2" style="display:none"
                    data-bs-toggle="modal" data-bs-target="#saveModal">
              Route speichern
            </button>

            <hr class="my-4" style="border-color: var(--border);">

            <div class="info-box">
              <small class="text-muted d-block">Drive safe:</small>
              <p class="small text-muted mt-2">Behalte stets die Straße im Blick und vor allem das Leaderboard.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card h-100 overflow-hidden">
          <div class="card-header bg-navy-mid border-bottom border-secondary d-flex justify-content-between align-items-center py-3">
            <span class="fw-bold">Karte</span>
            <div class="d-flex gap-2">
              <button id="clearBtn" class="btn btn-sm btn-outline-secondary">Wegpunkte löschen</button>
              <button id="fullscreenBtn" class="btn btn-sm btn-outline-secondary">⛶ Vollbild</button>
            </div>
          </div>
          <div class="card-body p-0" style="min-height:500px;">
            <div id="map" style="width:100%;height:100%;min-height:500px;"></div>
          </div>
        </div>
      </div>

    </div>
  </div>
  <!-- Route speichern Modal -->
  <div class="modal fade" id="saveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="background:var(--navy-mid);border:1px solid var(--border)">
        <div class="modal-header" style="border-color:var(--border)">
          <h5 class="modal-title">Route speichern</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label for="routeTitle" class="form-label">Titel</label>
          <input type="text" id="routeTitle" class="form-control" maxlength="100"
                 placeholder="z. B. Mannheim Rundkurs" />
          <div id="saveStatus" class="mt-2 small"></div>
        </div>
        <div class="modal-footer" style="border-color:var(--border)">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Abbrechen</button>
          <button type="button" id="saveConfirmBtn" class="btn btn-success">Speichern</button>
        </div>
      </div>
    </div>
  </div>
  <script src="./assets/js/auth.js"></script>
  <script src="./assets/js/map.js"></script>
</body>
</html>