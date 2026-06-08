# Dokumentation – Pacenote24

---

## Deckblatt

| |                                                                                                                  |
|---|------------------------------------------------------------------------------------------------------------------|
| **App-Name** | Pacenote24                                                                                                       |
| **Kurzbeschreibung** | Web-App zur Verwaltung und Freigabe von Rallye-Pacenotes und Strecken                                            |
| **Teamname** | Pacenote24                                                                                                       |
| **Teammitglieder** | Tyler Hörnig, Peter Nübel, Lars Pfitzenmeyer, Jaron Kemper, Leon Theuer, Moritz Creyaufmüller, Tim Burke-Lehmann |
| **Kurs** | MA-TINF25CS1                                                                                                     |
| **Dozent** | Dipl.-Ing. Udo Erdmann                                                                                           |
| **Datum** | 02.06.2026                                                                                                       |

---

## 1. Referenzdokument

### 1.1 Ziele

Pacenote24 ist eine Web-App zur digitalen Erfassung, Verwaltung und Freigabe von Rallye-Pacenotes. Fahrer und Beifahrer können Streckenrouten anlegen, mit Pacenotes anreichern und gezielt für andere Benutzer oder Gruppen freigeben. Ziel ist eine einfach bedienbare, sichere und plattformunabhängige Lösung, die den bisherigen Papier-basierten Prozess digitalisiert.

### 1.2 Funktionsrahmen

Die App bietet folgende Kernfunktionen:

- **Benutzerverwaltung**: Registrierung, Login/Logout, Passwortänderung
- **Sessionverwaltung**: Token-basierte Sessions mit konfigurierbarem Auto-Logout
- **Routenverwaltung**: Anlegen, Bearbeiten, Löschen und Abrufen von Strecken (inkl. JSON-Routendaten)
- **Pacenotes**: Erfassen und Speichern von Pacenotes je Strecke als JSON
- **Sichtbarkeiten**: Freigabe von Routen für einzelne Benutzer oder Gruppen
- **Rechteverwaltung**: Unterscheidung zwischen Admin und normalem Benutzer
- **Datenexport**: Routen und Pacenotes als JSON exportierbar
- **Responsive UI**: Bedienbar auf Desktop und mobilen Geräten

### 1.3 Projektrahmen

| | |
|---|---|
| **Typ** | Web-App (kein Framework) |
| **Client** | HTML5, CSS3, JavaScript (ES-Module), Bootstrap 5, Leaflet |
| **Server** | PHP 8.2+, Apache |
| **Datenbank** | MariaDB |
| **Laufzeitumgebung** | XAMPP (PHP 8.2.4, MariaDB 10.4.28) |

### 1.4 Umsetzungsstrategie

Die Anwendung folgt einer klassischen Drei-Schichten-Architektur:

- **Frontend** (`public/`): PHP/HTML-Seiten mit Bootstrap 5 und Vanilla-JavaScript (ES-Module), Leaflet für die Karte, AJAX-Kommunikation über `api.js`
- **API-Schicht** (`public/ajax/`): PHP-Endpunkte, die JSON entgegennehmen und zurückgeben
- **Backend** (`app/`): Services für Datenbankzugriffe, Session- und Rechteverwaltung

Die API ist vollständig in `docs/api.yaml` (OpenAPI 3.0.3) dokumentiert. Alle Endpunkte geben ein einheitliches `{ success, data/error }` Format zurück.

### 1.5 Aufgaben und Zuständigkeiten

| Bereich                      | Zuständig                          |
|------------------------------|------------------------------------|
| Datenbankschema & Services   | Tim Burke-Lehmann                  |
| API-Endpunkte                | Tim Burke-Lehmann                  |
| Authentifizierung & Sessions | Tyler Hörnig, Lars Pfitzenmeyer    |
| Frontend / UI                | Jaron Kemper, Moritz Creyaufmüller |
| Dokumentation                | Alle                               |
| Routengenerierung & Karte    | Leon Theuer                        |
| Pacenotegenerierung          | Peter Nübel                        |

---

## 2. Product Backlog

Siehe Github Projekt

---

## 3. Frontend – Aufbau und Bedienbarkeit

### 3.1 Seitenstruktur

| Seite | Pfad | Zugriff | Beschreibung |
|---|---|---|---|
| Startseite | `/public/home.php` | Öffentlich | Landing-Page |
| Login | `/public/login.php` | Gast | Anmeldeformular |
| Registrierung | `/public/registrieren.php` | Gast | Neues Konto erstellen |
| Karte | `/public/index.php` | Eingeloggt | Route bauen (Leaflet/BRouter) + speichern |
| Routen | `/public/routen.php` | Eingeloggt | Liste eigener/freigegebener Routen, JSON-Export |
| Viewer | `/public/navigation.php` | Eingeloggt | Pacenotes Kurve für Kurve, JSON-Import |
| Admin-Bereich | `/public/admin/adminpanel.php` | Admin | Benutzer-, Gruppen- und Sichtbarkeitsverwaltung |

### 3.2 Technologien

- **Bootstrap 5** für Layout, Navigation, Modals und UI-Komponenten
- **Leaflet** für die interaktive Karte, **BRouter** fürs Routing
- **Vanilla JavaScript (ES-Module)** für Logik und AJAX (kein Framework)
- **api.js** als zentrales Modul für alle API-Anfragen (inkl. 401-Auto-Logout)

### 3.3 Bedienung

1. Nicht eingeloggte Nutzer werden serverseitig (`requireAuth`) auf `login.php` geleitet
2. Nach erfolgreichem Login folgt die Weiterleitung in die App
3. Routen und Daten werden per AJAX geladen und dynamisch dargestellt (kein Seitenreload)
4. Aktionen (Erstellen, Bearbeiten, Löschen) lösen AJAX-Requests aus
5. Bestätigungen vor dem Löschen erfolgen über Bootstrap-Modals
6. Fehlermeldungen werden direkt im UI angezeigt

---

## 4. Verzeichnisstruktur

```
pacenote24/
├── public/                        # Öffentlich erreichbarer Webroot
│   ├── home.php                   # Startseite / Landing
│   ├── index.php                  # Karte: Route bauen + speichern (Leaflet/BRouter)
│   ├── routen.php                 # Routenübersicht (Liste, JSON-Export)
│   ├── navigation.php             # Pacenote-Viewer (JSON-Import)
│   ├── login.php                  # Login-Seite
│   ├── registrieren.php           # Registrierung
│   ├── navbar.php / head.php      # geteilte Layout-Partials
│   ├── admin/                     # Admin-Bereich (requireAdmin)
│   │   ├── _header.php            # Admin-Layout (Navbar, Breadcrumb)
│   │   ├── adminpanel.php         # Benutzerübersicht
│   │   ├── user_detail.php        # Benutzer bearbeiten/löschen
│   │   ├── pacenote_view.php      # Routenübersicht (Admin)
│   │   └── route_detail.php       # Route + Sichtbarkeiten
│   ├── errors/                    # 403.php, 404.php
│   ├── assets/
│   │   ├── css/                   # stylesheetmain.css, admin.css
│   │   └── js/                    # api.js, auth.js, map.js, homepage.js, validation.js
│   └── ajax/                      # API-Endpunkte (JSON)
│       ├── auth/
│       │   ├── login.php          # POST – Login
│       │   ├── logout.php         # POST – Logout
│       │   └── register.php       # POST – Registrierung (öffentlich)
│       ├── users.php              # GET – Benutzer lesen (Admin)
│       ├── users/
│       │   ├── create.php         # POST – Benutzer erstellen (Admin)
│       │   ├── update.php         # POST – Benutzer bearbeiten (Self/Admin)
│       │   └── delete.php         # POST – Benutzer löschen (Self/Admin)
│       ├── groups.php             # GET – Gruppen lesen
│       ├── groups/                # create / update / delete (Admin)
│       ├── routes.php             # GET – Routen lesen (zugriffsgefiltert)
│       ├── routes/
│       │   ├── create.php         # POST – Route erstellen (+ Pacenote-Generierung)
│       │   ├── update.php         # POST – Route bearbeiten (Owner/Admin)
│       │   ├── delete.php         # POST – Route löschen (Owner/Admin)
│       │   └── pacenotes.php      # GET/POST – Pacenotes lesen/speichern
│       ├── group-members.php      # GET – Gruppenmitgliedschaften lesen
│       ├── group-members/         # create / delete (Admin)
│       ├── track-visible-users.php
│       ├── track-visible-users/   # create / delete (Admin)
│       ├── track-visible-groups.php
│       └── track-visible-groups/  # create / delete (Admin)
│
├── app/                           # Anwendungslogik (nicht öffentlich)
│   ├── config/
│   │   └── config.php             # Datenbankverbindung, Session-Timeout
│   ├── helpers/
│   │   └── Request.php            # Eingabevalidierung (requireFields, requireValidEmail, ...)
│   ├── services/
│   │   ├── Database.php           # PDO-Singleton
│   │   ├── UserService.php        # CRUD Benutzer
│   │   ├── GroupService.php       # CRUD Gruppen
│   │   ├── SessionService.php     # Session erstellen, prüfen, verlängern, löschen
│   │   ├── RouteService.php       # CRUD Routen + Pacenotes + Zugriffsfilter
│   │   ├── PaceNoteService.php    # Pacenote-Generierung aus GeoJSON (Kurvenanalyse)
│   │   ├── GroupMemberService.php # Gruppenmitgliedschaften
│   │   ├── TrackVisibleUserService.php
│   │   └── TrackVisibleGroupService.php
│   └── session/
│       ├── auth.php               # isAuthenticated(), currentUser(), hasRole()
│       └── guard.php              # requireAuth(_API), requireAdmin(_API), requireSelforAdmin()
│
├── sql/
│   ├── schema.sql                 # Datenbankschema
│   └── demo_data.sql              # Testdaten
│
└── docs/
    ├── api.yaml                   # OpenAPI 3.0.3 Spezifikation
    └── dokumentation.md           # Diese Dokumentation
```

---

## 5. Datenstrukturen und Tabellen

### 5.1 Übersicht

```
users ──< sessions
users ──< tracks (owner_user_id)
users ──< group_member >── groups
users ──< track_visible_user >── tracks
groups ──< track_visible_group >── tracks
```

### 5.2 Tabellen

#### `users`
Speichert alle registrierten Benutzer.

| Spalte | Typ | Beschreibung |
|---|---|---|
| `user_id` | INT AUTO_INCREMENT PK | Eindeutige Benutzer-ID |
| `email` | VARCHAR(100) UNIQUE NOT NULL | E-Mail-Adresse (Login) |
| `pw_hash` | VARCHAR(255) NOT NULL | Passwort-Hash (`password_hash()`) |

---

#### `groups`
Benutzergruppen. Die Gruppe mit `group_id = 1` ist die Admin-Gruppe.

| Spalte | Typ | Beschreibung |
|---|---|---|
| `group_id` | INT AUTO_INCREMENT PK | Eindeutige Gruppen-ID |
| `name` | VARCHAR(50) UNIQUE NOT NULL | Gruppenname |

---

#### `sessions`
Token-basierte Sessions. Der `session_id` ist ein 64-Zeichen-Hex-Token.

| Spalte | Typ | Beschreibung |
|---|---|---|
| `session_id` | VARCHAR(64) PK | Zufällig generierter Token (`bin2hex(random_bytes(32))`) |
| `user_id` | INT NOT NULL FK | Verknüpfter Benutzer |
| `created_at` | TIMESTAMP | Erstellungszeitpunkt |
| `timeout` | TIMESTAMP NOT NULL | Ablaufzeitpunkt (aus `config.php`) |

---

#### `tracks`
Strecken mit zugehörigen Routen- und Pacenote-Daten.

| Spalte | Typ | Beschreibung |
|---|---|---|
| `route_id` | INT AUTO_INCREMENT PK | Eindeutige Routen-ID |
| `title` | VARCHAR(100) NULL | Optionaler Titel der Strecke |
| `owner_user_id` | INT NOT NULL FK | Ersteller der Route |
| `compiled_time` | TIMESTAMP | Zeitpunkt der letzten Änderung |
| `json_data` | JSON NOT NULL | Routengeometrie (BRouter-GeoJSON) |
| `waypoints` | JSON NULL | Vom Nutzer gesetzte Wegpunkte (lat/lng) |
| `distance_m` | INT NULL | Streckenlänge in Metern |
| `pacenotes_data` | JSON NULL | Generierte Pacenotes (`{ notes: [...] }`), optional |

---

#### `group_member`
Verknüpfungstabelle Benutzer ↔ Gruppe.

| Spalte | Typ | Beschreibung |
|---|---|---|
| `user_id` | INT NOT NULL FK | Benutzer |
| `group_id` | INT NOT NULL FK | Gruppe |
| PK | (user_id, group_id) | Zusammengesetzter Primärschlüssel |

---

#### `track_visible_user`
Legt fest, welche Benutzer eine Route sehen dürfen.

| Spalte | Typ | Beschreibung |
|---|---|---|
| `user_id` | INT NOT NULL FK | Benutzer, der Zugriff hat |
| `route_id` | INT NOT NULL FK | Freigegebene Route |
| PK | (user_id, route_id) | Zusammengesetzter Primärschlüssel |

---

#### `track_visible_group`
Legt fest, welche Gruppen eine Route sehen dürfen.

| Spalte | Typ | Beschreibung |
|---|---|---|
| `group_id` | INT NOT NULL FK | Gruppe, die Zugriff hat |
| `route_id` | INT NOT NULL FK | Freigegebene Route |
| PK | (group_id, route_id) | Zusammengesetzter Primärschlüssel |

---

### 5.3 Foreign Key Constraints

Alle Fremdschlüssel sind mit `ON DELETE CASCADE` definiert – beim Löschen eines Benutzers oder einer Gruppe werden alle abhängigen Einträge automatisch entfernt.
