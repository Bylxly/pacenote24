<?php

require_once __DIR__ . '/../services/SessionService.php';
session_start();
//löscht die session id zum user in der Datenbank
if (isset($_SESSION['token'])) {
    $sessionService = new SessionService();
    $sessionService->deleteSession($_SESSION['token']);

    //terminiert ganze session und alle deklariereten Session Variablen
    $_SESSION = array ();

    session_destroy();
    header("Location: /pacenote24/public/login.php");
}


?>