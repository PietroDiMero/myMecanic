<?php
session_start();

// Supprimer toutes les variables de session
$_SESSION = [];

// Supprimer le cookie de session s’il existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Redirection vers login
header("Location: /www/index.html");
exit;
