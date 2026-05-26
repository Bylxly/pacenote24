<?php
require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../services/SessionService.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // E-Mail und Passwort aus dem Formular holen (mit Fallback)
    $email = $_POST['email'] ?? '';
    $password = $_POST['pass'] ?? '';

    // Prüfung ob Daten gesendet wurden
    if (empty($email) || empty($password)) {
        header("Location: /pacenote24/public/login.php?status=error_empty");
        exit;
    }

    // UserService und SessionService instanziieren
    $userService = new UserService();
    $sessionService = new SessionService();

    // Benutzer-Daten anhand der E-Mail aus API holen
    $user = $userService->getUserByEmail($email);

    // Prüfen ob Benutzer gefunden wurde
    if ($user !== null) {
        
        // Passwort mit dem Hash aus der Datenbank vergleichen
        if (password_verify($password, $user['pw_hash'])) {
            // Passwort ist korrekt!
            
            // Session mit DB session ID aufbauen
            $dbSessionId = $sessionService->createSession((int)$user['user_id']);
            session_set_cookie_params([
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Strict'
                ]);
            session_start();
            session_regenerate_id(true);
            $_SESSION['user_name'] = $user['email']; 
            $_SESSION['account_id'] = $user['user_id'];
            $_SESSION['token'] = $dbSessionId;


            // weiterleiten
            header("Location: /pacenote24/public/login.php?status=success");
            exit;
        } else {
            // Passwort inkorrekt (Geändert zu bad credentials da sich sonst rückschlüsse auf db einträge ziehen lassen)
            header("Location: /pacenote24/public/login.php?status=error_bad_credentials");
            exit;
        }
    } else {
        // Benutzer/Email existiert nicht in der Datenbank (Geändert zu bad credentials da sich sonst rückschlüsse auf db einträge ziehen lassen)
        header("Location: /pacenote24/public/login.php?status=error_bad_credentials");
        exit;
    }
}
?>
