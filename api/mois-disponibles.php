<?php
require_once "db.php";
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT DISTINCT DATE_FORMAT(date_travail, '%Y-%m') AS mois
        FROM travaux
        ORDER BY mois DESC
    ");

    $mois = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        "success" => true,
        "mois" => $mois
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => 'Erreur mois'
    ]);
}
