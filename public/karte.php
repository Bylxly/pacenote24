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
  <script type="module" src="./assets/js/map.js"></script>
<?php include './include/footer.php'; ?>
</body>
</html>