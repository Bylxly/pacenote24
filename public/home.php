<?php
require_once __DIR__ . '/../app/session/guard.php';
$authStatus = isAuthenticated();
?>
<!DOCTYPE html>
<html lang="de">
  <head>
      <?php include 'head.php'; ?>
  </head>

  <body>
  <?php include 'navbar.php'; ?>

    <header class="page-header text-center">
      <div class="container py-4">
        <h1 class="fw-bold tracking-wider"><span>PACENOTES24.de</span></h1>
        <p>Planen. Navigieren. Asphalt beherrschen.</p>
        <div class="d-flex justify-content-center gap-3 mt-4">
          <a href="index.php" class="btn btn-primary px-4">ZUR KARTE</a>
        </div>
      </div>
    </header>

    <section class="container py-4">
      <div class="row g-4">

    <section id="about" class="container py-5 my-4">
      <div class="card">
        <div class="card-stripe"></div>
        <div class="card-body-inner p-4 p-lg-5">
          <div class="row align-items-center g-5">
            
            <div class="col-lg-7">
              <div class="card-slot mb-2">SYSTEM // BACKGROUND</div>
              <h2 class="fw-bold mb-4" style="font-size: 2rem;">ÜBER <span>PACENOTES24</span></h2>
              
              <p class="mb-3">
                Wir glauben nicht an langweilige Routen von A nach B. Für uns zählt die Dynamik zwischen den Koordinaten. Hinter <strong>Pacenotes24.de</strong> steht ein Team aus Entwicklern und Kurvenliebhabern, die eine Plattform vermisst haben, die Straße nicht nur als Distanz, sondern als Abfolge von fahrtechnischen Herausforderungen zu sehen.
              </p>

                <p class="mb-3">
                    <b>Vertraut von den besten Fahrern der Welt: von Profis wie Lars Pfizenmayer bis hin zu ambitionierten Amateuren wie Max Verstappen.</b>
                </p>
              
              <p class="mb-3 text-muted">
                Pacenotes24.de ist ein reines Werkzeug zur Routenplanung und Streckenvisualisierung. Die Nutzung der bereitgestellten Informationen und Karten erfolgt ausschließlich auf eigene Gefahr und nach eigenem Ermessen des jeweiligen Fahrers. 
                Jeder Nutzer trägt zu jedem Zeitpunkt die volle Eigenverantwortung für sein Handeln, die Beherrschung seines Fahrzeugs sowie die Einhaltung der geltenden Straßenverkehrsordnung (StVO) und aller lokalen Gesetze. Die Inhalte dieser Plattform sind weder als Aufforderung noch als Anleitung zu aggressivem, risikoreichem oder gesetzeswidrigem Fahren zu verstehen. Bitte fahre stets vorausschauend, rücksichtsvoll und den Witterungs- sowie Straßenverhältnissen angepasst.
              </p>
            </div>

            <ddiv class="col-lg-5">
              <div class="route-card__image shadow" style="border-radius: 12px; border: 1px solid var(--border); aspect-ratio: 4 / 3;">
                <img src="./assets/img/Lars_Pfizenmayer.png" alt="Pacenotes24 Streckenplanung" class="img-fluid" />
              </div>
            </div> 
          </div>
        </div>
      </div>
    </section>
  </body>
</html>