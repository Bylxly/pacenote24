<?php
require_once __DIR__ . '/../app/session/guard.php';
requireGuest();
?>

<html lang="de">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pacenotes24.de</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="./assets/css/stylesheetmain.css" rel="stylesheet" />
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/polyline/1.2.0/polyline.min.js"></script> 
  </head>

  <body>
    <nav class="navbar navbar-expand-lg px-3">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Pacenotes24<span>.de</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
          <ul class="navbar-nav me-auto">
            <li class="nav-item"><a class="nav-link active" href="landing_page.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="index.php">Karte</a></li>
            <li class="nav-item"><a class="nav-link" href="routen.php">Routen</a></li>
            <li class="nav-item"><a class="nav-link" href="navigation.php">Navigation</a></li>
          </ul>
          <a href="../app/session/LogoutHandler.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
      </div>
    </nav>

    <header class="page-header text-center">
      <div class="container py-4">
        <h1 class="fw-bold tracking-wider"><span>PACENOTES24.de</span></h1>
        <p>Planen. Navigieren. Asphalt beherrschen.</p>
        <div class="d-flex justify-content-center gap-3 mt-4">
          <a href="index.php" class="btn btn-primary px-4">ZUR KARTE</a>
        </div>
      </div>
    </header>


    <section id="about" class="container py-5 my-4">
      <div class="card">
        <div class="card-stripe"></div>
        <div class="card-body-inner p-4 p-lg-5">
          <div class="row align-items-center g-5">
            
            <div class="col-lg-7">
              <div class="card-slot mb-2">BACKGROUND // DISCLAIMER</div>
              <h2 class="fw-bold mb-4" style="font-size: 2rem;">ÜBER <span>PACENOTES24</span></h2>
              
              <p class="mb-3">
                Wir glauben nicht an langweilige Routen von A nach B. Für uns zählt die Dynamik zwischen den Koordinaten. Hinter <strong>Pacenotes24.de</strong> steht ein Team aus Entwicklern und Kurvenliebhabern, die eine Plattform vermisst haben, die Straße nicht nur als Distanz, sondern als Abfolge von fahrtechnischen Herausforderungen zu sehen.
              </p>
              
              <p class="mb-3 text-muted">
                  Pacenotes24.de ist ein reines Werkzeug zur Routenplanung und Streckenvisualisierung. Die Nutzung der bereitgestellten Informationen und Karten erfolgt ausschließlich auf eigene Gefahr und nach eigenem Ermessen des jeweiligen Fahrers.
                  Jeder Nutzer trägt zu jedem Zeitpunkt die volle Eigenverantwortung für sein Handeln, die Beherrschung seines Fahrzeugs sowie die Einhaltung der geltenden Straßenverkehrsordnung (StVO) und aller lokalen Gesetze. Die Inhalte dieser Plattform sind weder als Aufforderung noch als Anleitung zu aggressivem, risikoreichem oder gesetzeswidrigem Fahren zu verstehen. Bitte fahre stets vorausschauend, rücksichtsvoll und den Witterungs- sowie Straßenverhältnissen angepasst.
              </p>

            </div>

            <div class="col-lg-5">
                <div class="route-card__image shadow" style="border-radius: 12px; border: 1px solid var(--border); aspect-ratio: 4 / 3;">
                  <img src="./assets/img/dein-bild.jpg" alt="Bild Lars" class="img-fluid" />
                </div>
            </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </body>
</html>