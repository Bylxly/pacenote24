# Pacenote24

Web-App zur Planung, Verwaltung und Freigabe von Rallye-**Pacenotes**. Nutzer
zeichnen auf einer interaktiven Karte Routen, lassen daraus automatisch Pacenotes
(Kurven mit Richtung, Schweregrad, Distanz) generieren und sehen sie Kurve für Kurve
im Viewer.

Projekt im Rahmen *Web-/App-Engineering* (Kurs MA-TINF25CS1).

---

## Features

- **Sessionverwaltung** mit konfigurierbarem Auto-Logout
- **Login / Logout / Registrierung** (Passwörter als bcrypt-Hash)
- **Rechteverwaltung** (Admin vs. User) über Gruppenmitgliedschaft
- **Admin-Bereich**: Benutzer-, Gruppen- und Sichtbarkeitsverwaltung
- **Routen-CRUD** mit interaktiver Leaflet-Karte + BRouter-Routing
- **Automatische Pacenote-Generierung** aus der Streckengeometrie (`PaceNoteService`)
- **Pacenote-Viewer** (Kurve für Kurve, Pfeil/Severity/Distanz)
- **JSON-Export & -Import** von Pacenotes
- **Zugriffsgefilterte Routenliste** (man sieht nur eigene/freigegebene Routen)
- Client- und serverseitige Eingabevalidierung (RegExp)
- Responsives Design mit Bootstrap 5

## Tech-Stack

| Schicht | Technologie |
|---|---|
| Frontend | HTML5, CSS3, JavaScript (ES-Module), Bootstrap 5, Leaflet |
| Backend | PHP 8.2+, PDO |
| Datenbank | MariaDB |
| Laufzeit | XAMPP (PHP 8.2.4, MariaDB 10.4.28) |
| API | AJAX (JSON), dokumentiert in `docs/api.yaml` (OpenAPI 3.0.3) |

---

## Projektstruktur

```
pacenote24/
├── public/                     # Öffentlich erreichbarer Webroot
│   ├── index.php               # Karte: Route bauen + speichern
│   ├── routen.php              # Routenübersicht (Liste, Export)
│   ├── navigation.php          # Pacenote-Viewer
│   ├── home.php                # Startseite
│   ├── login.php               # Login
│   ├── registrieren.php        # Registrierung
│   ├── profil.php              # Eigenes Profil (E-Mail/Passwort ändern, Account löschen)
│   ├── navbar.php / navbar-guest.php / head.php  # geteilte Partials
│   ├── admin/                  # Admin-Bereich
│   │   ├── _header.php         # Admin-Layout (Navbar, Breadcrumb)
│   │   ├── adminpanel.php      # Benutzerübersicht
│   │   ├── user_detail.php     # Benutzer bearbeiten/löschen
│   │   ├── groups.php          # Gruppenverwaltung
│   │   ├── pacenote_view.php   # Routenübersicht (Admin)
│   │   └── route_detail.php    # Route + Sichtbarkeiten
│   ├── ajax/                   # JSON-API-Endpunkte
│   │   ├── auth/               # login, logout, register, change-password
│   │   ├── users(.php)         # CRUD Benutzer
│   │   ├── groups(.php)        # CRUD Gruppen
│   │   ├── routes(.php)        # CRUD Routen + pacenotes.php
│   │   ├── group-members(.php) # Gruppenmitgliedschaften
│   │   └── track-visible-*(.php)# Sichtbarkeiten (User/Gruppe)
│   ├── assets/
│   │   ├── css/                # stylesheetmain.css, admin.css
│   │   └── js/                 # api.js, auth.js, map.js, homepage.js, validation.js
│   └── errors/                 # 403.php, 404.php
│
├── app/                        # Anwendungslogik (nicht öffentlich)
│   ├── config/config.php       # Zentrale Konfiguration (DB, Session-Timeout)
│   ├── helpers/Request.php     # Eingabevalidierung
│   ├── services/               # Fachlogik / DB-Zugriff (PDO)
│   │   ├── Database.php
│   │   ├── UserService.php  GroupService.php  SessionService.php
│   │   ├── RouteService.php  PaceNoteService.php
│   │   ├── GroupMemberService.php
│   │   └── TrackVisibleUserService.php  TrackVisibleGroupService.php
│   └── session/
│       ├── auth.php            # isAuthenticated(), currentUser(), hasRole()
│       └── guard.php           # requireAuth(_API), requireAdmin(_API), requireSelforAdmin()
│
├── sql/
│   ├── schema.sql              # Datenbankschema
│   ├── demo_data.sql           # Demo-Daten (inkl. Login-Accounts)
│   └── datenbankschema.md      # Schema-Doku
│
└── docs/
    ├── api.yaml                # OpenAPI-Spezifikation
    ├── setup.md                # Installationsanleitung
    └── dokumentation.md        # Projektdokumentation
```

---

## Installation

Ausführliche Schritt-für-Schritt-Anleitung in **[`docs/setup.md`](docs/setup.md)**.

Kurzfassung:
1. Repo nach `htdocs/` (bzw. den Projektordner) entpacken
2. In XAMPP **Apache** + **MySQL** starten
3. In phpMyAdmin Datenbank `pacenote24` anlegen
4. `sql/schema.sql` importieren, danach `sql/demo_data.sql`
5. DB-Zugang ggf. in `app/config/config.php` prüfen
6. Aufrufen: `http://localhost/public/`

## Demo-Zugänge

| E-Mail | Passwort | Rolle |
|---|---|---|
| `admin@test.de` | `Admin123!` | Admin |
| `user1@test.de` | `User123!` | User |
| `user2@test.de` | `User123!` | User |

> Verfügbar nach Import von `sql/demo_data.sql`.

---

## Dokumentation

- **[docs/setup.md](docs/setup.md)** - Installation & DB-Import
- **[docs/api.yaml](docs/api.yaml)** - API-Endpunkte (OpenAPI)
- **[docs/dokumentation.md](docs/dokumentation.md)** - Projektdokumentation
