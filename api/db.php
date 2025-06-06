<?php

require_once 'login.php'; // tu peux commenter ça si inutile

$host = 'localhost'; // ✅ ou récupère depuis Hostinger si nécessaire
$dbname = 'u177740262_myMecanic';
$user = 'u177740262_Pietro';
$pass = 'Fjp4xrdmaque.';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // ✅ Connexion réussie
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion']);
    exit;
}
