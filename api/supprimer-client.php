<?php
require_once 'db.php';
require_once 'class/class.php';

header('Content-Type: application/json');


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID invalide']);
    exit;
}

$id = intval($_GET['id']);
$client = new Client($pdo); // ou $mysql selon ton db.php

try {
    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Client supprimé avec succès']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de suppression client ']);
}
