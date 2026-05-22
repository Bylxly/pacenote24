<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../services/UserService.php';
    require_once __DIR__ . '/../services/SessionService.php';
    // E-Mail und Passwort aus dem Formular holen (mit Fallback)
    $email = $_POST['email'] ?? '';
    $password = $_POST['pass'] ?? '';

    // Prüfung ob Daten gesendet wurden
    if (empty($email) || empty($password)) {
        header("Location: /pacenote24/public/login.html?status=error_empty");
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
            
            // Session mit DB session ID neu aufbauen
            session_destroy();
            $dbSessionId = $sessionService->createSession($user['user_id']);
            session_set_cookie_params([
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
                ]);
            session_id($dbSessionId);
            session_start();
            $_SESSION['user_name'] = $user['email']; 
            $_SESSION['account_id'] = $user['user_id'];
                    

            // weiterleiten
            header("Location: /pacenote24/public/login.html?status=success");
            exit;
        } else {
            // Passwort inkorrekt
            header("Location: /pacenote24/public/login.html?status=error_pw");
            exit;
        }
    } else {
        // Benutzer/Email existiert nicht in der Datenbank
        header("Location: /pacenote24/public/login.html?status=error_username");
        exit;
    }
}
?>
