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

    <section id="about" class="container py-5 my-4">
      <div class="card">
        <div class="card-stripe"></div>
        <div class="card-body-inner p-4 p-lg-5">
          <div class="row align-items-center g-5">
            
            <div class="col-lg-7">
              <div class="card-slot mb-2"></div>
              <h2 class="fw-bold mb-4" style="font-size: 2rem;">ÜBER <span>PACENOTES24</span></h2>
              
              <p class="mb-3">
                Wir glauben nicht an langweilige Routen von A nach B. Für uns zählt die Dynamik zwischen den Koordinaten. Hinter <strong>PACENOTES24.de</strong> steht ein Team aus Entwicklern und Kurvenliebhabern, die eine Plattform vermisst haben, die Straße nicht nur als Distanz, sondern als Abfolge von fahrtechnischen Herausforderungen zu sehen.
              </p>

                <p class="mb-3">
                    <b>Vertraut von den besten Fahrern der Welt: von Profis wie Lars Pfizenmayer bis hin zu ambitionierten Amateuren wie Max Verstappen.</b>
                </p>
              
              <p class="mb-3 text-muted">
                Pacenotes24.de ist ein reines Werkzeug zur Routenplanung und Streckenvisualisierung. Die Nutzung der bereitgestellten Informationen und Karten erfolgt ausschließlich auf eigene Gefahr und nach eigenem Ermessen des jeweiligen Fahrers.
                Jeder Nutzer trägt zu jedem Zeitpunkt die volle Eigenverantwortung für sein Handeln, die Beherrschung seines Fahrzeugs sowie die Einhaltung der geltenden Straßenverkehrsordnung (StVO) und aller lokalen Gesetze. Die Inhalte dieser Plattform sind weder als Aufforderung noch als Anleitung zu aggressivem, risikoreichem oder gesetzeswidrigem Fahren zu verstehen. Bitte fahre stets vorausschauend, rücksichtsvoll und den Witterungs- sowie Straßenverhältnissen angepasst.
              </p>
            </div>

            <div class="col-lg-5">
              <div class="route-card__image shadow" style="border-radius: 12px; border: 1px solid var(--border); aspect-ratio: 4 / 3;">
                <img src="./assets/img/Lars_Pfizenmayer.png" alt="Pacenotes24 Streckenplanung" class="img-fluid" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

  <!--Legal Disclaimer -->
  <section id="legal-counsel" class="container py-5 my-4">
    <div class="card">
      <div class="card-stripe"></div>
      <div class="card-body-inner p-4 p-lg-5">
        <div class="row align-items-center g-5">

          <!-- Anwaltsbild -->
          <div class="col-lg-4 text-center">
            <div class="legal-counsel-img mx-auto mb-3">
              <!-- Bild des Anwalts hier einfügen: -->
              <img src="./assets/img/Anwalt.jpg"
                   alt="Rechtsanwalt – Ansprechpartner für rechtliche Fragen"
                   class="img-fluid"
                   onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
              <div class="legal-counsel-placeholder" style="display:none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
                <span style="font-size:.8rem; color:var(--muted); margin-top:.5rem;">Anwaltsfoto</span>
              </div>
            </div>
            <p class="legal-counsel-name mb-0">Rechtsanwalt</p>
            <p class="legal-counsel-title">Fachanwalt für IT- &amp; Verkehrsrecht</p>
          </div>

          <!-- Anwaltliche Einschätzung -->
          <div class="col-lg-8">
            <div class="card-slot mb-2">RECHTLICHER HINWEIS</div>
            <h2 class="fw-bold mb-4" style="font-size:1.8rem;">ANWALTLICHE <span>EINSCHÄTZUNG</span></h2>
            <div class="legal-quote mb-4">
              <p>„Pacenotes24.de stellt ausschließlich eine technische Visualisierungsplattform für Geodaten dar.
              Die bereitgestellten Inhalte begründen keinerlei Aufforderung zu rechtswidrigem Verhalten.
              Nutzer sind vollumfänglich selbst für die Einhaltung der StVO sowie aller anwendbaren Gesetze verantwortlich.
              Eine Haftung der Plattformbetreiber für Schäden, die aus der Nutzung dieser Informationen entstehen, ist gesetzlich ausgeschlossen."</p>
            </div>

            <p class="text-muted" style="font-size:.82rem;">
              Diese rechtliche Einschätzung wurde von einem unabhängigen Fachanwalt erstellt und spiegelt den Stand der deutschen Rechtsprechung zum Zeitpunkt der Veröffentlichung wider.
              Sie ersetzt keine individuelle Rechtsberatung.
            </p>
          </div>

        </div>
      </div>
    </div>
  </section>

  <!--Footer-->
  <footer class="site-footer mt-5">
    <div class="footer-top">
      <div class="container py-5">
        <div class="row g-5">

          <!-- Spalte 1 -->
          <div class="col-lg-4">
            <p class="footer-brand mb-3">Pacenotes24<span>.de</span></p>
            <p class="footer-text">
              Pacenotes24.de ist ein unabhängiges Werkzeug zur Streckenvisualisierung und Routenplanung.
              Die Plattform richtet sich ausschließlich an Nutzer, die Geodaten zu Informationszwecken abrufen.
            </p>
            <p class="footer-text mt-3" style="font-size:.78rem;">
              &copy; <?= date('Y') ?> Pacenotes24.de &mdash; Alle Rechte vorbehalten.
            </p>
          </div>

          <!-- Spalte 2: Haftungsausschluss -->
          <div class="col-lg-4">
            <h6 class="footer-heading">Haftungsausschluss</h6>
            <p class="footer-text">
              Die Nutzung aller auf dieser Plattform bereitgestellten Informationen, Karten und Streckenverläufe
              erfolgt <strong>ausschließlich auf eigene Gefahr</strong> des jeweiligen Nutzers.
            </p>
            <p class="footer-text mt-2">
              Der Betreiber übernimmt keinerlei Haftung für Personen-, Sach- oder Vermögensschäden,
              die unmittelbar oder mittelbar aus der Nutzung dieser Plattform entstehen.
              Dies gilt insbesondere für Unfälle, Ordnungswidrigkeiten oder Straftatbestände,
              die durch das Fahrverhalten des Nutzers verursacht werden.
            </p>
            <p class="footer-text mt-2">
              Alle Inhalte sind rein informativ und stellen weder eine Anleitung zu
              aggressivem Fahren noch eine Aufforderung zu Verstößen gegen die Straßenverkehrsordnung (StVO) dar.
            </p>
          </div>

          <!-- Spalte 3: Rechtliches & Links -->
          <div class="col-lg-4">
            <h6 class="footer-heading">Rechtliches</h6>
            <ul class="footer-links">
              <li><a href="#legal-counsel">Anwaltliche Einschätzung</a></li>
              <li><a href="#about">Über Pacenotes24</a></li>
              <li><a href="impressum.php">Impressum</a></li>
              <li><a href="datenschutz.php">Datenschutzerklärung</a></li>
              <li><a href="nutzungsbedingungen.php">Nutzungsbedingungen</a></li>
            </ul>

            <h6 class="footer-heading mt-4">Wichtige Hinweise</h6>
            <ul class="footer-disclaimer-list">
              <li>Fahre stets der StVO entsprechend.</li>
              <li>Beachte Witterungs- und Straßenverhältnisse.</li>
              <li>Übernimm zu jeder Zeit die volle Eigenverantwortung.</li>
              <li>Diese Plattform ersetzt keine professionelle Fahrerausbildung.</li>
            </ul>
          </div>

        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="container py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
          <span class="footer-legal-note">
            Pacenotes24.de haftet nicht für die Aktualität, Richtigkeit oder Vollständigkeit der bereitgestellten Kartendaten.
            Kartenmaterial &copy; OpenStreetMap-Mitwirkende.
          </span>
          <span class="footer-version">v1.0</span>
        </div>
      </div>
    </div>
  </footer>

  </body>
</html>