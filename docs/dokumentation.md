# Dokumentation – Pacenote24

---

## Deckblatt

| | |
|---|---|
| **App-Name** | Pacenote24 |
| **Kurzbeschreibung** | Web-App zur Verwaltung und Freigabe von Rallye-Pacenotes und Strecken |
| **Teamname** | Pacenote24 |
| **Teammitglieder** | <!-- Namen ergänzen --> |
| **Kurs** | MA-TINF25CS1 |
| **Dozent** | Dipl.-Ing. Udo Erdmann |
| **Datum** | 02.06.2026 |

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
| **Client** | HTML5, CSS3, JavaScript, Bootstrap, jQuery |
| **Server** | PHP 8.2+, Apache |
| **Datenbank** | MariaDB |
| **Laufzeitumgebung** | XAMPP (PHP 8.2.4, MariaDB 10.4.28) |

### 1.4 Umsetzungsstrategie

Die Anwendung folgt einer klassischen Drei-Schichten-Architektur:

- **Frontend** (`public/`): HTML-Seiten mit Bootstrap und jQuery, AJAX-Kommunikation über `api.js`
- **API-Schicht** (`public/ajax/`): PHP-Endpunkte, die JSON entgegennehmen und zurückgeben
- **Backend** (`app/`): Services für Datenbankzugriffe, Session- und Rechteverwaltung

Die API ist vollständig in `docs/api.yaml` (OpenAPI 3.0.3) dokumentiert. Alle Endpunkte geben ein einheitliches `{ success, data/error }` Format zurück.

### 1.5 Aufgaben und Zuständigkeiten

| Bereich | Zuständig |
|---|---|
| Datenbankschema & Services | <!-- Name --> |
| API-Endpunkte | <!-- Name --> |
| Authentifizierung & Sessions | <!-- Name --> |
| Frontend / UI | <!-- Name --> |
| Dokumentation | <!-- Name --> |

---

## 2. Product Backlog

| ID | User Story | Priorität | Status |
|---|---|---|---|
| PB-01 | Als Benutzer möchte ich mich einloggen können | Hoch | ✅ Done |
| PB-02 | Als Benutzer möchte ich mich ausloggen können | Hoch | ✅ Done |
| PB-03 | Als Benutzer werde ich nach Inaktivität automatisch ausgeloggt | Hoch | ✅ Done |
| PB-04 | Als Admin möchte ich alle Benutzer sehen | Hoch | ✅ Done |
| PB-05 | Als Admin möchte ich Benutzer erstellen | Hoch | ✅ Done |
| PB-06 | Als Admin möchte ich Benutzer löschen | Hoch | ✅ Done |
| PB-07 | Als Benutzer möchte ich mein Passwort ändern | Hoch | ✅ Done |
| PB-08 | Als Benutzer möchte ich eine Route anlegen | Hoch | ✅ Done |
| PB-09 | Als Benutzer möchte ich meine Routen abrufen | Hoch | ✅ Done |
| PB-10 | Als Benutzer möchte ich eine Route bearbeiten | Hoch | ✅ Done |
| PB-11 | Als Benutzer möchte ich eine Route löschen | Hoch | ✅ Done |
| PB-12 | Als Benutzer möchte ich Pacenotes zu einer Route speichern | Hoch | ✅ Done |
| PB-13 | Als Benutzer möchte ich Pacenotes einer Route abrufen | Hoch | ✅ Done |
| PB-14 | Als Admin möchte ich eine Route für Benutzer freigeben | Mittel | ✅ Done |
| PB-15 | Als Admin möchte ich eine Route für Gruppen freigeben | Mittel | ✅ Done |
| PB-16 | Als Admin möchte ich Gruppen verwalten | Mittel | ✅ Done |
| PB-17 | Als Admin möchte ich Benutzer zu Gruppen hinzufügen | Mittel | ✅ Done |
| PB-18 | Als Benutzer sehe ich nur freigegebene Routen | Hoch | ✅ Done |
| PB-19 | Als Benutzer erhalte ich Fehlermeldungen bei ungültiger Eingabe | Hoch | ✅ Done |
| PB-20 | Als Benutzer kann ich Routen/Pacenotes als JSON exportieren | Mittel | 🔄 Offen |
| PB-21 | Als Benutzer sehe ich eine responsive Oberfläche | Mittel | 🔄 Offen |
| PB-22 | Als Benutzer erhalte ich Bestätigungsdialoge vor dem Löschen | Mittel | 🔄 Offen |

---

## 3. Frontend – Aufbau und Bedienbarkeit

### 3.1 Seitenstruktur

| Seite | Pfad | Zugriff | Beschreibung |
|---|---|---|---|
| Login | `/public/login.html` | Öffentlich | Anmeldeformular |
| Startseite | `/public/index.php` | Eingeloggt | Übersicht der eigenen Routen |
| Admin-Bereich | `/public/admin.php` | Admin | Benutzerverwaltung, Gruppen |

### 3.2 Technologien

- **Bootstrap** für Layout, Navigation und UI-Komponenten
- **jQuery** für DOM-Manipulation und AJAX
- **api.js** als zentrales Modul für alle API-Anfragen

### 3.3 Bedienung

1. Benutzer öffnet die App und wird auf `/login.html` weitergeleitet (falls nicht eingeloggt)
2. Nach erfolgreichem Login wird auf `/index.php` weitergeleitet
3. Routen werden per AJAX geladen und dynamisch in die Seite eingebettet
4. Aktionen (Erstellen, Bearbeiten, Löschen) lösen AJAX-Requests aus
5. Bestätigungsdialoge werden vor destruktiven Aktionen angezeigt
6. Fehlermeldungen werden direkt im UI dargestellt (kein Seitenreload)

---

## 4. Verzeichnisstruktur

```
pacenote24/
├── public/                        # Öffentlich erreichbarer Webroot
│   ├── index.php                  # Startseite (eingeloggt)
│   ├── login.html                 # Login-Seite
│   ├── assets/
│   │   ├── css/                   # Stylesheets
│   │   ├── js/
│   │   │   └── api.js             # Zentrales API-Modul (fetch-Wrapper)
│   │   └── img/
│   └── ajax/                      # API-Endpunkte
│       ├── auth/
│       │   ├── login.php          # POST – Login
│       │   └── logout.php         # POST – Logout
│       ├── users.php              # GET – Benutzer lesen (Admin)
│       ├── users/
│       │   ├── create.php         # POST – Benutzer erstellen (Admin)
│       │   ├── update.php         # POST – Benutzer bearbeiten (Self/Admin)
│       │   └── delete.php         # POST – Benutzer löschen (Self/Admin)
│       ├── groups.php             # GET – Gruppen lesen
│       ├── groups/
│       │   ├── create.php         # POST – Gruppe erstellen (Admin)
│       │   ├── update.php         # POST – Gruppe bearbeiten (Admin)
│       │   └── delete.php         # POST – Gruppe löschen (Admin)
│       ├── routes.php             # GET – Routen lesen
│       ├── routes/
│       │   ├── create.php         # POST – Route erstellen
│       │   ├── update.php         # POST – Route bearbeiten (Owner/Admin)
│       │   ├── delete.php         # POST – Route löschen (Owner/Admin)
│       │   └── pacenotes.php      # GET/POST – Pacenotes lesen/speichern
│       ├── sessions.php           # GET – Sessions lesen (Admin)
│       ├── group-members.php      # GET – Gruppenmitgliedschaften lesen
│       ├── group-members/
│       │   ├── create.php         # POST – Mitglied hinzufügen (Admin)
│       │   └── delete.php         # POST – Mitglied entfernen (Admin)
│       ├── track-visible-users.php
│       ├── track-visible-users/
│       │   ├── create.php         # POST – Freigabe für User (Admin)
│       │   └── delete.php         # POST – Freigabe entfernen (Admin)
│       ├── track-visible-groups.php
│       └── track-visible-groups/
│           ├── create.php         # POST – Freigabe für Gruppe (Admin)
│           └── delete.php         # POST – Freigabe entfernen (Admin)
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
│   │   ├── RouteService.php       # CRUD Routen + Pacenotes
│   │   ├── GroupMemberService.php # Gruppenmitgliedschaften
│   │   ├── TrackVisibleUserService.php
│   │   └── TrackVisibleGroupService.php
│   └── session/
│       ├── auth.php               # isAuthenticated(), currentUser(), hasRole()
│       └── guard.php              # requireAuth_API(), requireAdmin(), requireSelforAdmin()
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
| `json_data` | JSON NOT NULL | Routendaten (GPS, Wegpunkte, etc.) |
| `pacenote_data` | JSON NULL | Pacenotes zur Route (optional) |

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
