<?php
require_once __DIR__ . '/../app/session/guard.php'; 
requireAuth();

?>
<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Homepage</title>
    <!-- Bootstrap CSS -->
    <link
       href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
       rel="stylesheet"
    />
    <!-- Custom CSS-->
    <link
      href="assets/css/stylsheetmain.css"
      rel="stylesheet"
    />
    <script src="assets/js/homepage.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/homepage.js" defer></script>
  </head>

  <body>
  <nav class="navbar navbar-expand-lg px-3">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.html">Pastenotes24.de</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link active" href="index.html">Karte</a></li>
          <li class="nav-item"><a class="nav-link" href="routen.html">Routen</a></li>
          <li class="nav-item"><a class="nav-link" href="leaderboard.html">Leaderboard</a></li>
        </ul>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
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
            <button class="btn btn-primary w-100">Route berechnen</button>
            
            <hr class="my-4" style="border-color: var(--border);">
            
            <div class="info-box">
              <small class="text-muted d-block">Drive safe:</small>
              <p class="small text-muted mt-2"> Behalte stehts die Straße im Blick und vorallem das Leaderboard
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card h-100 overflow-hidden">
          <div class="card-header bg-navy-mid border-bottom border-secondary d-flex justify-content-between align-items-center py-3">
            <span class="fw-bold">Karte</span>
            <button class="btn btn-sm btn-outline-secondary" onclick="vollbild()">⛶ Vollbild</button>
          </div>
          <div class="card-body p-0">
            <div class="ratio ratio-16x9" id="kartenContainer" style="min-height: 500px;">
              <iframe
                id="karte"
                src="https://www.openstreetmap.org/export/embed.html?bbox=8.45,49.47,8.5,49.50&layer=mapnik"
                allowfullscreen>
              </iframe>
            </div>
          </div>
        </div>
      </div>

    </div> </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>