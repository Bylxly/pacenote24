# Setup - Pacenote24

## Voraussetzungen

- [XAMPP](https://www.apachefriends.org/) mit **PHP 8.2.4** und **MariaDB 10.4.28**
- Ein Browser (Chrome, Firefox, Edge)

---

## Schritt 1 - Dateien entpacken

Das Archiv in das XAMPP-Webverzeichnis entpacken:

```
C:\xampp\htdocs\            (Windows)
/Applications/XAMPP/xamppfiles/htdocs/   (macOS)
```

Nach dem Entpacken sollte folgende Struktur vorhanden sein:

```
htdocs/
└── pacenote24/
    ├── public/
    ├── app/
    ├── sql/
    └── docs/
```

---

## Schritt 2 - XAMPP starten

1. XAMPP Control Panel öffnen
2. **Apache** starten
3. **MySQL** starten

---

## Schritt 3 - Datenbank einrichten

### 3.1 Datenbank anlegen

1. Im Browser öffnen: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Links auf **„Neu"** klicken
3. Datenbankname: `pacenote24`
4. Zeichensatz: `utf8mb4_general_ci`
5. **„Anlegen"** klicken

### 3.2 Schema importieren

1. Die Datenbank `pacenote24` in der linken Leiste auswählen
2. Oben auf den Reiter **„Importieren"** klicken
3. Auf **„Datei auswählen"** klicken
4. Datei `sql/schema.sql` aus dem Projektordner auswählen
5. **„Importieren"** klicken

### 3.3 Testdaten importieren (optional)

Für Demo-Daten denselben Vorgang mit `sql/demo_data.sql` wiederholen.

> Alternativ über die Kommandozeile:
> ```bash
> mysql -u root -p pacenote24 < sql/schema.sql
> mysql -u root -p pacenote24 < sql/demo_data.sql
> ```

---

## Schritt 4 - Konfiguration anpassen

Die Datei `app/config/config.php` öffnen und die Datenbankzugangsdaten prüfen:

```php
return [
    // ...
    'database' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'dbname'   => 'pacenote24',
        'charset'  => 'utf8mb4',
        'username' => 'root',
        'password' => '',           // Standard-XAMPP: kein Passwort
    ],
    'session' => [
        'name'            => 'pacenote24_session',
        'timeout_seconds' => 1800,  // Auto-Logout nach 30 Minuten
    ],
];
```

---

## Schritt 5 - Anwendung starten

Im Browser öffnen:

```
http://localhost/pacenote24/public/
```

Die Anwendung leitet automatisch auf die Login-Seite weiter.

---

## Standard-Zugangsdaten (Demo-Daten)

| E-Mail | Passwort | Rolle |
|---|---|---|
| `admin@test.de` | `Admin123!` | Admin |
| `user1@test.de` | `User123!` | Benutzer |
| `user2@test.de` | `User123!` | Benutzer |

> Diese Zugangsdaten sind nur nach dem Import von `demo_data.sql` verfügbar.

---

## Häufige Probleme

**Seite lädt nicht / 404**
- Prüfen ob Apache in XAMPP läuft
- Prüfen ob der Ordnername exakt `pacenote24` lautet

**Datenbankfehler**
- Prüfen ob MySQL in XAMPP läuft
- Zugangsdaten in `app/config/config.php` prüfen
- Prüfen ob die Datenbank `pacenote24` angelegt wurde

**Login schlägt fehl nach Demo-Import**
- Sicherstellen, dass `demo_data.sql` nach `schema.sql` importiert wurde
