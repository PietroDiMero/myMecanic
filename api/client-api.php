<?php


require_once 'db.php';
require_once 'class/class.php';

header('Content-Type: application/json');

$client = new Client($pdo); // ✅ on utilise bien $pdo

if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {
    $id = intval($_GET['id']);
    $fiche = $client->getClient($id);
    echo json_encode($fiche ?: ['error' => 'Client introuvable']);
} else {
    $liste = $client->getAll();
    echo json_encode($liste);
}
