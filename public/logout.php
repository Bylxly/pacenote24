<?php
session_start();
//terminiert ganze session und alle deklariereten Session Variablen
$_SESSION = array ();

session_destroy();
header("Location: login.html");



?>