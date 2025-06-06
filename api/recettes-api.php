<?php
require_once 'db.php';
header('Content-Type: application/json');

$mois = $_GET['mois'] ?? null;

try {
    if ($mois) {
        $stmt = $pdo->prepare("
            SELECT t.id, t.date_travail, t.total, t.description, t.statut,
                   c.nom, c.prenom
            FROM travaux t
            JOIN clients c ON c.id = t.client_id
            WHERE t.statut = 'terminé'
              AND DATE_FORMAT(t.date_travail, '%Y-%m') = ?
            ORDER BY t.date_travail DESC
        ");
        $stmt->execute([$mois]);
    } else {
        $stmt = $pdo->query("
            SELECT t.id, t.date_travail, t.total, t.description, t.statut,
                   c.nom, c.prenom
            FROM travaux t
            JOIN clients c ON c.id = t.client_id
            WHERE t.statut = 'terminé'
            ORDER BY t.date_travail DESC
        ");
    }

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur ']);
}
