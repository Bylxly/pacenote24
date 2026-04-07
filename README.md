# pacenote24

## Project structure
```
project-root/
├── public/                        # Öffentlich erreichbarer Webroot
│   ├── index.php                  # Einstiegspunkt der Anwendung
│   ├── login.php                  # Login-Seite
│   ├── logout.php                 # Logout-Logik
│   ├── assets/                    # Statische Dateien
│   │   ├── css/                   # Stylesheets
│   │   ├── js/                    # JavaScript-Dateien
│   │   └── img/                   # Bilder und Icons
│   ├── ajax/                      # AJAX-Endpunkte / API-nahe Requests
│   │   ├── auth.php               # Authentifizierung
│   │   ├── users.php              # Benutzerverwaltung
│   │   ├── routes.php             # Routenverwaltung
│   │   ├── pacenotes.php          # Pace Notes / Streckennotizen
│   │   └── export.php             # Export-Funktionen
│   ├── manifest.json              # Web-App-Metadaten / PWA-Konfiguration
│   └── service-worker.js          # Service Worker für Offline-/Cache-Funktionen
│
├── app/                           # Anwendungslogik
│   ├── config/
│   │   └── config.php             # Zentrale Konfiguration
│   ├── controllers/               # Controller-Schicht
│   ├── models/                    # Datenmodelle
│   ├── services/                  # Fachlogik / Business Services
│   │   ├── AuthService.php        # Login, Session, Berechtigungen
│   │   ├── UserService.php        # Benutzerbezogene Logik
│   │   ├── RouteService.php       # Logik rund um Routen
│   │   ├── PaceNoteService.php    # Logik für Pace Notes
│   │   └── ExportService.php      # Export-Funktionen
│   ├── views/                     # Templates / Ausgabe
│   └── helpers/                   # Hilfsfunktionen / Utilities
│
├── sql/                           # Datenbankskripte
│   ├── schema.sql                 # Datenbankschema
│   └── demo_data.sql              # Demo- und Testdaten
│
├── docs/                          # Projektdokumentation
└── tests/                         # Tests
```
# pacenote24

## Project structure
project-root/
├── public/                        # Öffentlich erreichbarer Webroot
│   ├── index.php                  # Einstiegspunkt der Anwendung
│   ├── login.php                  # Login-Seite
│   ├── logout.php                 # Logout-Logik
│   ├── assets/                    # Statische Dateien
│   │   ├── css/                   # Stylesheets
│   │   ├── js/                    # JavaScript-Dateien
│   │   └── img/                   # Bilder und Icons
│   ├── ajax/                      # AJAX-Endpunkte / API-nahe Requests
│   │   ├── auth.php               # Authentifizierung
│   │   ├── users.php              # Benutzerverwaltung
│   │   ├── routes.php             # Routenverwaltung
│   │   ├── pacenotes.php          # Pace Notes / Streckennotizen
│   │   └── export.php             # Export-Funktionen
│   ├── manifest.json              # Web-App-Metadaten / PWA-Konfiguration
│   └── service-worker.js          # Service Worker für Offline-/Cache-Funktionen
│
├── app/                           # Anwendungslogik
│   ├── config/
│   │   └── config.php             # Zentrale Konfiguration
│   ├── controllers/               # Controller-Schicht
│   ├── models/                    # Datenmodelle
│   ├── services/                  # Fachlogik / Business Services
│   │   ├── AuthService.php        # Login, Session, Berechtigungen
│   │   ├── UserService.php        # Benutzerbezogene Logik
│   │   ├── RouteService.php       # Logik rund um Routen
│   │   ├── PaceNoteService.php    # Logik für Pace Notes
│   │   └── ExportService.php      # Export-Funktionen
│   ├── views/                     # Templates / Ausgabe
│   └── helpers/                   # Hilfsfunktionen / Utilities
│
├── sql/                           # Datenbankskripte
│   ├── schema.sql                 # Datenbankschema
│   └── demo_data.sql              # Demo- und Testdaten
│
├── docs/                          # Projektdokumentation
└── tests/                         # Tests