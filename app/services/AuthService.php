<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// funktion zum überprüfen ob der nutzer eingeloggt ist und eine session hat, ansonsten weiterleitung zur login seite
function isloggedin ()
{
if(!isset($_SESSION['user_name'] ) || $_SESSION['user_loggedin'] !== true) {
    header("Location: /pacenote24/public/login.html?status=not_logged_in");
    exit; 

}
}

function isinactive() {


}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/UserService.php';

    // E-Mail und Passwort aus dem Formular holen (mit Fallback)
    $email = $_POST['email'] ?? '';
    $password = $_POST['pass'] ?? '';

    // Prüfung ob Daten gesendet wurden
    if (empty($email) || empty($password)) {
        header("Location: login.html?status=error_empty");
        exit;
    }

    // UserService instanziieren
    $userService = new UserService();

    // Benutzer-Daten anhand der E-Mail aus API holen
    $user = $userService->getUserByEmail($email);

    // Prüfen ob Benutzer gefunden wurde
    if ($user !== null) {
        
        // Passwort mit dem Hash aus der Datenbank vergleichen
        if (password_verify($password, $user['pw_hash'])) {
            // Passwort ist korrekt!
            
            // Session-Variablen deklarieren
            $_SESSION['user_loggedin'] = TRUE;
            session_regenerate_id(true);
            $_SESSION['user_name'] = $user['email']; 
            
            $_SESSION['account_id'] = $user['user_id']; 
            
            // weiterleiten
            header("Location: /pacenote24/public/login.html?status=success");
            exit;
        } else {
            // Passwort inkorrekt
            header("Location: /pacenote24/public/login.html?status=error_pw");
            $_SESSION['test'] = "false";
            exit;
        }
    } else {
        // Benutzer/Email existiert nicht in der Datenbank
        header("Location: /pacenote24/public/login.html?status=error_username");
        exit;
    }
}
?>
