<?php
require_once __DIR__ . '/../app/session/guard.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <?php include './include/head.php'; ?>
</head>

<body>
<?php include './include/navbar.php'; ?>
  <!-- Impressum -->
  <section id="impressum" class="container py-5">
    <div class="card">
      <div class="card-stripe"></div>
      <div class="card-body-inner p-4 p-lg-5">
        <div class="card-slot">RECHTLICHES</div>
        <h2 class="fw-bold mb-4" style="font-size:1.8rem;">IMPRESSUM</h2>

        <div class="row g-4">
          <div class="col-md-6">
            <h6 style="color:var(--accent);text-transform:uppercase;letter-spacing:.08em;font-size:.8rem;" class="mb-3">Angaben gemäß § 5 TMG</h6>
            <p class="mb-1" style="color:var(--muted);font-size:.9rem;"><strong style="color:var(--text);">Betreiber:</strong> Lars Pfitzenmeyer</p>
            <p class="mb-1" style="color:var(--muted);font-size:.9rem;"><strong style="color:var(--text);">Anschrift:</strong> Pfitzenmeyer-Straße 24, 242424 Pfitzenbach</p>
            <p class="mb-0 mt-3" style="color:var(--muted);font-size:.9rem;"><strong style="color:var(--text);">Verantwortlich für den Inhalt (§ 55 Abs. 2 RStV):</strong><br>Pfitz Larsenmeyer</p>
          </div>
          <div class="col-md-6">
            <h6 style="color:var(--accent);text-transform:uppercase;letter-spacing:.08em;font-size:.8rem;" class="mb-3">Haftungshinweis</h6>
            <p style="color:var(--muted);font-size:.88rem;line-height:1.7;">
              Trotz sorgfältiger inhaltlicher Kontrolle übernehmen wir keine Haftung für die Inhalte externer Links.
              Für den Inhalt verlinkter Seiten sind ausschließlich deren Betreiber verantwortlich.
            </p>
            <p style="color:var(--muted);font-size:.88rem;line-height:1.7;" class="mt-2">
              Die auf dieser Plattform bereitgestellten Kartendaten stammen von
              OpenStreetMap-Mitwirkenden und unterliegen der Open Database Licence (ODbL).
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Datenschutzerklärung -->
  <section id="datenschutz" class="container py-5">
    <div class="card">
      <div class="card-stripe"></div>
      <div class="card-body-inner p-4 p-lg-5">
        <div class="card-slot">DSGVO</div>
        <h2 class="fw-bold mb-4" style="font-size:1.8rem;">DATENSCHUTZ<span>ERKLÄRUNG</span></h2>

        <div class="row g-5">
          <div class="col-lg-6">
            <h6 style="color:var(--accent);text-transform:uppercase;letter-spacing:.08em;font-size:.8rem;" class="mb-3">1. Verantwortlicher</h6>
            <p style="color:var(--muted);font-size:.88rem;line-height:1.75;">
              Verantwortlicher im Sinne der DSGVO ist der unter dem Impressum angegebene Betreiber dieser Website.
            </p>

            <h6 style="color:var(--accent);text-transform:uppercase;letter-spacing:.08em;font-size:.8rem;" class="mt-4 mb-3">2. Erhobene Daten</h6>
            <p style="color:var(--muted);font-size:.88rem;line-height:1.75;">
              Beim Besuch dieser Website werden automatisch technische Zugriffsdaten (IP-Adresse, Browsertyp, Uhrzeit)
              im Server-Log erfasst. Diese Daten dienen ausschließlich der Sicherstellung des Betriebs und werden
              nicht mit personenbezogenen Profilen verknüpft.
            </p>
            <p style="color:var(--muted);font-size:.88rem;line-height:1.75;" class="mt-2">
              Bei der Registrierung und Anmeldung werden E-Mail-Adresse und Passwort (gehasht) gespeichert.
              Diese Daten sind für den Betrieb des Nutzerkontos erforderlich (Art. 6 Abs. 1 lit. b DSGVO).
            </p>

            <h6 style="color:var(--accent);text-transform:uppercase;letter-spacing:.08em;font-size:.8rem;" class="mt-4 mb-3">3. Cookies &amp; lokaler Speicher</h6>
            <p style="color:var(--muted);font-size:.88rem;line-height:1.75;">
              Diese Plattform setzt ausschließlich technisch notwendige Session-Cookies ein,
              die nach dem Schließen des Browsers ablaufen. Es werden keine Tracking- oder Werbe-Cookies verwendet.
            </p>
          </div>

          <div class="col-lg-6">
            <h6 style="color:var(--accent);text-transform:uppercase;letter-spacing:.08em;font-size:.8rem;" class="mb-3">4. Drittanbieter – OpenStreetMap</h6>
            <p style="color:var(--muted);font-size:.88rem;line-height:1.75;">
              Diese Website bindet Kartenmaterial von OpenStreetMap (OSM) ein.
              Beim Laden der Karten wird eine Verbindung zu den OSM-Tile-Servern hergestellt,
              dabei wird Ihre IP-Adresse übermittelt. Weitere Informationen finden Sie in der
              <a href="https://wiki.osmfoundation.org/wiki/Privacy_Policy" target="_blank" rel="noopener" style="color:var(--accent);">Datenschutzerklärung der OpenStreetMap Foundation</a>.
            </p>

            <h6 style="color:var(--accent);text-transform:uppercase;letter-spacing:.08em;font-size:.8rem;" class="mt-4 mb-3">5. Rechte der betroffenen Personen</h6>
            <p style="color:var(--muted);font-size:.88rem;line-height:1.75;">
              Sie haben gemäß DSGVO das Recht auf Auskunft (Art. 15), Berichtigung (Art. 16),
              Löschung (Art. 17), Einschränkung der Verarbeitung (Art. 18), Datenübertragbarkeit (Art. 20)
              sowie Widerspruch (Art. 21). Zur Ausübung Ihrer Rechte wenden Sie sich bitte an die
              im Impressum angegebene E-Mail-Adresse.
            </p>

            <h6 style="color:var(--accent);text-transform:uppercase;letter-spacing:.08em;font-size:.8rem;" class="mt-4 mb-3">6. Beschwerderecht</h6>
            <p style="color:var(--muted);font-size:.88rem;line-height:1.75;">
              Sie haben das Recht, sich bei einer Datenschutz-Aufsichtsbehörde über die Verarbeitung
              Ihrer personenbezogenen Daten zu beschweren.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Nutzungsbedingungen -->
  <section id="nutzungsbedingungen" class="container py-5 mb-4">
    <div class="card">
      <div class="card-stripe"></div>
      <div class="card-body-inner p-4 p-lg-5">
        <div class="card-slot">AGB</div>
        <h2 class="fw-bold mb-4" style="font-size:1.8rem;">NUTZUNGS<span>BEDINGUNGEN</span></h2>

        <div class="row g-5">
          <div class="col-lg-6">
            <h6 style="color:var(--accent);text-transform:uppercase;letter-spacing:.08em;font-size:.8rem;" class="mb-3">1. Geltungsbereich</h6>
            <p style="color:var(--muted);font-size:.88rem;line-height:1.75;">
              Diese Nutzungsbedingungen gelten für alle Nutzer der Plattform Pacenotes24.de.
              Mit der Nutzung der Plattform erklärt sich der Nutzer mit diesen Bedingungen einverstanden.
            </p>

            <h6 style="color:var(--accent);text-transform:uppercase;letter-spacing:.08em;font-size:.8rem;" class="mt-4 mb-3">2. Leistungsumfang</h6>
            <p style="color:var(--muted);font-size:.88rem;line-height:1.75;">
              Pacenotes24.de stellt eine technische Plattform zur Visualisierung von Geodaten und
              Streckenverläufen bereit. Die Plattform dient ausschließlich Informationszwecken.
              Ein Anspruch auf dauerhaften Betrieb oder ununterbrochene Verfügbarkeit besteht nicht.
            </p>

            <h6 style="color:var(--accent);text-transform:uppercase;letter-spacing:.08em;font-size:.8rem;" class="mt-4 mb-3">3. Pflichten des Nutzers</h6>
            <p style="color:var(--muted);font-size:.88rem;line-height:1.75;">
              Der Nutzer verpflichtet sich, die Plattform ausschließlich zu legalen Zwecken zu nutzen
              und keine Inhalte hochzuladen, die gegen geltendes Recht, die guten Sitten oder
              Rechte Dritter verstoßen. Das automatisierte Auslesen von Daten (Scraping) ist untersagt.
            </p>
          </div>

          <div class="col-lg-6">
            <h6 style="color:var(--accent);text-transform:uppercase;letter-spacing:.08em;font-size:.8rem;" class="mb-3">4. Haftungsbeschränkung</h6>
            <p style="color:var(--muted);font-size:.88rem;line-height:1.75;">
              Der Betreiber haftet nicht für Schäden, die aus der Nutzung oder Nichtnutzung der
              bereitgestellten Informationen entstehen. Dies gilt insbesondere für Schäden,
              die durch ein Fehlverhalten im Straßenverkehr entstehen.
            </p>

            <h6 style="color:var(--accent);text-transform:uppercase;letter-spacing:.08em;font-size:.8rem;" class="mt-4 mb-3">5. Änderungen der Nutzungsbedingungen</h6>
            <p style="color:var(--muted);font-size:.88rem;line-height:1.75;">
              Der Betreiber behält sich vor, diese Nutzungsbedingungen jederzeit zu ändern.
              Die jeweils aktuelle Version ist auf dieser Seite abrufbar.
              Die weitere Nutzung der Plattform nach einer Änderung gilt als Zustimmung zur neuen Fassung.
            </p>

            <h6 style="color:var(--accent);text-transform:uppercase;letter-spacing:.08em;font-size:.8rem;" class="mt-4 mb-3">6. Anwendbares Recht</h6>
            <p style="color:var(--muted);font-size:.88rem;line-height:1.75;">
              Es gilt das Recht der Bundesrepublik Deutschland. Gerichtsstand ist, soweit gesetzlich
              zulässig, der Sitz des Betreibers.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>